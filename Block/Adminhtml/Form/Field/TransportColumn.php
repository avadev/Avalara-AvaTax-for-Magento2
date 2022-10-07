<?php
declare(strict_types=1);

namespace ClassyLlama\AvaTax\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObjectFactory;
use ClassyLlama\AvaTax\Api\RestDefinitionsInterface;
use ClassyLlama\AvaTax\Helper\Config;
/**
 * @codeCoverageIgnore
 */
class TransportColumn extends Select
{
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;
    /**
     * @var RestDefinitionsInterface
     */
    protected $definitionsService;
    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DataObjectFactory $dataObjectFactory
     * @param RestDefinitionsInterface $definitionsService
     * @param Config              $config
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        StoreManagerInterface $storeManager,
        DataObjectFactory $dataObjectFactory,
        RestDefinitionsInterface $definitionsService,
        Config $config,
        array $data = []
    )
    { 
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->definitionsService = $definitionsService;
        $this->config = $config;
    }
    
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }
    /**
     * Retrieve parameters options from Avatax API
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        $storeId = $this->storeManager->getStore()->getId();
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $isProduction = null;
        $validateResult = [];
        $accountNumber = $this->config->getAccountNumber($storeId, $scopeType, $isProduction);
        $licenseKey = $this->config->getLicenseKey($storeId, $scopeType, $isProduction);
        if(!empty($accountNumber) && !empty($licenseKey))
        {
            $validateResult = $this->definitionsService->parameters($isProduction, $storeId);
        }
        $transport = [];
        if(!empty($validateResult) && $validateResult->hasValue())
        {
            foreach ($validateResult->getValue() as $objValue)
            {
                if($objValue->hasValues())
                {
                    foreach($objValue->getValues() as $transportValue)
                    {
                        $transport[] = ['label' => $transportValue, 'value' => $transportValue];
                    }
                }
            }
        }
        return $transport;
    }
}