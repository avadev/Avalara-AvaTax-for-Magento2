<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */
namespace ClassyLlama\AvaTax\Block\Adminhtml\Customer\Edit;

use Magento\Framework\View\Element\Template\Context;
use Magento\Backend\Model\UrlInterface as BackendUrl;
use ClassyLlama\AvaTax\Helper\Config as ConfigHelper;

/**
 * @codeCoverageIgnore
 */
class Region extends \Magento\Framework\View\Element\Template
{
    /**
     * @var BackendUrl
     */
    private $backendUrl;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * Region constructor.
     * @param Context $context
     * @param BackendUrl $backendUrl
     * @param ConfigHelper $configHelper
     */
    public function __construct(Context $context, BackendUrl $backendUrl, ConfigHelper $configHelper)
    {
        $this->backendUrl = $backendUrl;
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getRegionUrl()
    {
        return $url = $this->backendUrl->getUrl('avatax/address/region');
    }

    /**
     * @return mixed
     */
    public function isAddressValidationEnabled()
    {
        return $this->configHelper->isAddressValidationEnabled($this->_storeManager->getStore());
    }
}
