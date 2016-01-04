<?php

namespace ClassyLlama\AvaTax\Block;

use ClassyLlama\AvaTax\Model\Config;
use Magento\Framework\View\Element\Template\Context;

class CustomerAddress extends \Magento\Framework\View\Element\Template {

    /**
    * @var Config
    */
    protected $config = null;

    /**
     * CustomerAddress constructor.
     * @param Context $context
     * @param array $data
     * @param Config $config
     */
    public function __construct(
        Context $context,
        array $data = [],
        Config $config
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    public function getStoreCode() {
        return $this->_storeManager->getStore()->getCode();
    }

    public function isValidationEnabled() {
        return $this->config->isAddressValidationEnabled();
    }

    public function getChoice() {
        return $this->config->allowUserToChooseAddress();
    }

    public function getInstructions() {
        if ($this->getChoice()) {
            return addslashes($this->config->getAddressValidationInstructionsWithChoice());
        } else {
            return addslashes($this->config->getAddressValidationInstructionsWithOutChoice());
        }
    }

    public function getErrorInstructions() {
        return addslashes($this->config->getAddressValidationErrorInstructions());
    }

    public function getCountriesEnabled() {
        return $this->config->getAddressValidationCountriesEnabled();
    }
}