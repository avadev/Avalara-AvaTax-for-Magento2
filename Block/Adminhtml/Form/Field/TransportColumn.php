<?php
declare(strict_types=1);

namespace ClassyLlama\AvaTax\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObjectFactory;
use ClassyLlama\AvaTax\Api\RestDefinitionsInterface;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Helper\ApiLog;

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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var RestDefinitionsInterface
     */
    protected $definitionsService;
    /**
     * @var ApiLog
     */
    protected $apiLog;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DataObjectFactory $dataObjectFactory
     * @param RestDefinitionsInterface $definitionsService
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Config              $config
     * @param ApiLog $apiLog
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        StoreManagerInterface $storeManager,
        DataObjectFactory $dataObjectFactory,
        RestDefinitionsInterface $definitionsService,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Config $config,
        ApiLog $apiLog,
        array $data = []
    )
    { 
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->definitionsService = $definitionsService;
        $this->messageManager = $messageManager;
        $this->config = $config;
        $this->apiLog = $apiLog;
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
            try {
                $validateResult = $this->definitionsService->parameters($isProduction, $storeId);
            } catch (\Exception $e) {
                $debugLogContext = [];
                $debugLogContext['message'] = $e->getMessage();
                $debugLogContext['source'] = 'TransportColumn';
                $debugLogContext['operation'] = 'Block_Adminhtml_Form_Field_TransportColumn';
                $debugLogContext['function_name'] = 'getSourceOptions';
                $this->apiLog->debugLog($debugLogContext, $storeId, $scopeType);
                $this->messageManager->addError($e->getMessage());
                return [];
            }
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