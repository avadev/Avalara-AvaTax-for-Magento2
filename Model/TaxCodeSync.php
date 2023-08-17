<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model;

use Magento\Framework\Model\AbstractModel;
use ClassyLlama\AvaTax\Api\RestDefinitionsInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Helper\Config;

/**
 * Class TaxCodeSync
 * @package ClassyLlama\AvaTax\Model
 */
class TaxCodeSync extends AbstractModel
{
    /**
     * @var RestDefinitionsInterface
     */
    protected $definitionsService;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param AvaTaxLogger $avaTaxLogger
     * @param RestDefinitionsInterface $definitionsService
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AvaTaxLogger $avaTaxLogger,
        RestDefinitionsInterface $definitionsService,
        Config $config,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->definitionsService = $definitionsService;
        $this->config = $config;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('ClassyLlama\AvaTax\Model\ResourceModel\TaxCodeSync');
    }

    /**
     * Call an AvaTax API to sync product and shipping tax class codes
     *
     * @param int             $companyId
     * @param bool|null       $isProduction
     * @param string|int|null $scopeId
     * @param string|null     $scopeType
     * @param boolean         $fetchGlobalTax
     * @return int
     */
    public function synchTaxCodes(
        $companyId,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $fetchGlobalTax = true
        )
    {
        $taxCodes = [];

        if($fetchGlobalTax){
            // Api call to get Avalara tax codes
            $globalTaxCodes = $this->definitionsService->getTaxCodes($isProduction, $scopeId, $scopeType);

            if (!empty($globalTaxCodes) && $globalTaxCodes->hasValue()) {
                foreach ($globalTaxCodes->getValue() as $taxCodeRecord) {
                    $taxCodes[] = [
                        'company_id' => $taxCodeRecord->getCompanyId(),
                        'tax_code' => $taxCodeRecord->getTaxCode(),
                        'description' => $taxCodeRecord->getDescription(),
                        'tax_code_type_id' => $taxCodeRecord->getTaxCodeTypeId(),
                        'is_active' => $taxCodeRecord->getIsActive() ? 1 : 0
                    ];
                }
            }
        }
        
        // Api call to get Company Specific Custom tax codes
        $customTaxCodes = $this->definitionsService->getCustomTaxCodes($companyId, $isProduction, $scopeId, $scopeType);

        if (!empty($customTaxCodes) && $customTaxCodes->hasValue()) {
            foreach ($customTaxCodes->getValue() as $taxCodeRecord) {
                $taxCodes[] = [
                    'company_id' => $taxCodeRecord->getCompanyId(),
                    'tax_code' => $taxCodeRecord->getTaxCode(),
                    'description' => $taxCodeRecord->getDescription(),
                    'tax_code_type_id' => $taxCodeRecord->getTaxCodeTypeId(),
                    'is_active' => $taxCodeRecord->getIsActive() ? 1 : 0
                ];
            }
        }

        // Save tax codes
        $result = $this->getResource()->saveTaxCodes($taxCodes);
        return $result;
    }

    /**
     * Retrieve companyId's config data by path
     *
     * @return array
     */
    public function getConfigCompanies(){
        // Retrieve saved avatax company ids config with development mode
        $devCompanies = $this->getResource()->getConfigCompanies($this->config::XML_PATH_AVATAX_DEVELOPMENT_COMPANY_ID);

        // Retrieve saved avatax company ids config with production mode
        $prodCompanies = $this->getResource()->getConfigCompanies($this->config::XML_PATH_AVATAX_PRODUCTION_COMPANY_ID);

        if (!empty($devCompanies) && count($devCompanies) > 0) {
            foreach($devCompanies as $key => $company){
                $isProduction = $this->config->isProductionMode($company['scope_id'], $company['scope']);
                if($isProduction){
                    unset($devCompanies[$key]);
                }
            }
        }
        
        if (!empty($prodCompanies) && count($prodCompanies) > 0) {
            foreach($prodCompanies as $key => $company){
                $isProduction = $this->config->isProductionMode($company['scope_id'], $company['scope']);
                if(!$isProduction){
                    unset($prodCompanies[$key]);
                }
            }
        }

        return array_merge($devCompanies, $prodCompanies);
    }
}