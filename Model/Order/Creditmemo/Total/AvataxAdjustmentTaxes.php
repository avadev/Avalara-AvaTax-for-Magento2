<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Model\Order\Creditmemo\Total;

use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Request\CreditmemoRequestBuilder;
use ClassyLlama\AvaTax\Api\RestTaxInterface;
use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\TaxCompositeInterface;
use Magento\Store\Model\ScopeInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Request\Request as CreditmemoRequest;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax\Result as RestTaxResult;
use ClassyLlama\AvaTax\Framework\Interaction\Line as FrameworkInteractionLine;

/**
 * Class AvataxAdjustmentTaxes
 * @package ClassyLlama\AvaTax\Model\Order\Creditmemo\Total
 */
class AvataxAdjustmentTaxes extends AbstractTotal
{

    /**
     * @var string
     */
    const ADJUSTMENTS_CONFIG_PATH = 'tax/avatax_advanced/avatax_adjustment_taxes';

    /**
     * @var array
     */
    const ADJUSTMENTS_TAXES_MAP = [
        FrameworkInteractionLine::ADJUSTMENT_POSITIVE_LINE_DESCRIPTION => 'adjustment_refund',
        FrameworkInteractionLine::ADJUSTMENT_NEGATIVE_LINE_DESCRIPTION => 'adjustment_fee'
    ];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CreditmemoRequestBuilder
     */
    private $creditmemoRequestBuilder;

    /**
     * @var TaxCompositeInterface
     */
    private $taxCompositeService;

    /**
     * @var AvaTaxLogger
     */
    private $avataxLogger;

    /**
     * AvataxAdjustmentTaxes constructor.
     * @param AvaTaxLogger $avataxLogger
     * @param TaxCompositeInterface $taxCompositeService
     * @param CreditmemoRequestBuilder $creditmemoRequestBuilder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        AvaTaxLogger $avataxLogger,
        TaxCompositeInterface $taxCompositeService,
        CreditmemoRequestBuilder $creditmemoRequestBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($data);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->creditmemoRequestBuilder = $creditmemoRequestBuilder;
        $this->taxCompositeService = $taxCompositeService;
        $this->avataxLogger = $avataxLogger;
    }

    /**
     * Collect Adjustment Refund | Adjustment Fee taxes
     *
     * @param Creditmemo $creditmemo
     * @return AvataxAdjustmentTaxes
     */
    public function collect(Creditmemo $creditmemo): self
    {
        if ($this->isTaxCalculationEnabledForAdjustments() && $this->adjustmentsAreNotEmpty($creditmemo)) {
            try {

                /** @var CreditmemoRequest|null $creditmemoRequest */
                $creditmemoRequest = $this->creditmemoRequestBuilder->build($creditmemo);

                if (null !== $creditmemoRequest) {
                    /** @var int $storeId */
                    $storeId = (int)$creditmemo->getStoreId();
                    /** @var RestTaxResult $response */
                    $response = $this->taxCompositeService->calculateTax(
                        $creditmemoRequest,
                        $storeId,
                        ScopeInterface::SCOPE_STORE,
                        [RestTaxInterface::FLAG_FORCE_NEW_RATES => true],
                        null
                    );
                    $this->avataxLogger->debug(
                        'response',
                        [
                            'lines' => print_r($response->getLines() ?? [], true)]
                    );
                    /** @var float|null $adjustmentRefundTax */
                    $adjustmentRefundTax = $baseAdjustmentRefundTax = (float)$this->getAdjustmentRefundTaxes($this->getCreditmemoTaxesForAdjustments($response));
                    /** @var float|null $adjustmentFeeTax */
                    $adjustmentFeeTax = $baseAdjustmentFeeTax = (float)$this->getAdjustmentFeeTaxes($this->getCreditmemoTaxesForAdjustments($response));

                    /**
                     * To calculate taxes:
                     * - adjustment refund tax (positive adjustment) - taxes we have to add to the total Credit Memo taxes
                     * - adjustment fee tax (negative adjustment) - taxes we have to subtract from the total Credit memo taxes
                     */
                    $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $adjustmentRefundTax - $adjustmentFeeTax);
                    $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseAdjustmentRefundTax - $baseAdjustmentFeeTax);
                }
            } catch (\Throwable $exception) {
                $this->avataxLogger->error($exception->getMessage(), [
                    'class' => self::class,
                    'trace' => $exception->getTraceAsString()
                ]);
            }
        }

        return $this;
    }

    /**
     * Check if taxes calculations were enabled for Adjustment Refund | Adjustment Fee within the admin panel
     *
     * @return bool
     */
    private function isTaxCalculationEnabledForAdjustments(): bool
    {
        try {
            return (bool)$this->scopeConfig->getValue(
                self::ADJUSTMENTS_CONFIG_PATH,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $this->getStoreId()
            );
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * Check whether Adjustment Refund or Adjustment Fee is not empty
     * We will make an API call to the Avalara if at least one adjustment (Adjustment Refund | Adjustment Fee) will be set
     *
     * @param Creditmemo $creditmemo
     * @return bool
     */
    private function adjustmentsAreNotEmpty(Creditmemo $creditmemo): bool
    {
        return !empty((float)$creditmemo->getBaseAdjustmentPositive()) || !empty((float)$creditmemo->getBaseAdjustmentNegative());
    }

    /**
     * Get Store id
     *
     * @return int|null
     */
    private function getStoreId()
    {
        try {
            return (int)$this->storeManager->getStore()->getId();
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * Get estimated Credit Memo Adjustment Refund and Adjustment Fee taxes, which were calculated by Avalara
     *
     * @param RestTaxResult|null $response
     * @return array
     */
    private function getCreditmemoTaxesForAdjustments(RestTaxResult $response = null): array
    {
        if (null !== $response) {
            $adjustmentsTaxes = [];
            /** @var array|null $lines */
            if (!empty((array)$lines = $response->getLines())) {
                /** @var FrameworkInteractionLine $line */
                foreach ($lines as $line) {
                    if (!empty((string)$description = $line->getDescription()) && array_key_exists((string)$description, self::ADJUSTMENTS_TAXES_MAP)) {
                        $adjustmentsTaxes[self::ADJUSTMENTS_TAXES_MAP[$description]] = $line;
                    }
                }
            }
            return $adjustmentsTaxes;
        }
        return [];
    }

    /**
     * Get Adjustment Refund taxes
     *
     * @param array $creditmemoTaxesForAdjustments
     * @return float|null
     */
    private function getAdjustmentRefundTaxes(array $creditmemoTaxesForAdjustments = [])
    {
        if (!empty($creditmemoTaxesForAdjustments)) {
            /** @var DataObject|null $adjustmentRefund */
            $adjustmentRefund = $creditmemoTaxesForAdjustments['adjustment_refund'] ?? null;
            if ($adjustmentRefund instanceof DataObject) {
                return abs((float)$adjustmentRefund->getData('tax_calculated'));
            }
        }

        return null;
    }

    /**
     * Get Adjustment Fee taxes
     *
     * @param array $creditmemoTaxesForAdjustments
     * @return float|null
     */
    private function getAdjustmentFeeTaxes(array $creditmemoTaxesForAdjustments = [])
    {
        if (!empty($creditmemoTaxesForAdjustments)) {
            /** @var DataObject|null $adjustmentFee */
            $adjustmentFee = $creditmemoTaxesForAdjustments['adjustment_fee'] ?? null;
            if ($adjustmentFee instanceof DataObject) {
                return abs((float)$adjustmentFee->getData('tax_calculated'));
            }
        }

        return null;
    }
}
