<?php

namespace ClassyLlama\AvaTax\Block;

use ClassyLlama\AvaTax\Helper\Config;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlInterface;

class CustomerAddress extends \Magento\Framework\View\Element\Template
{

    /**
     * Validate address path
     */
    const VALIDATE_ADDRESS_PATH = 'avatax/address/validation';

    /**
    * @var Config
    */
    protected $config = null;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * CustomerAddress constructor
     * @param Context $context
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param Config $config
     */
    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        array $data = [],
        Config $config
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
    }

    /**
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @return string
     */
    public function getStoreCode() {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @return mixed
     */
    public function isValidationEnabled() {
        return $this->config->isAddressValidationEnabled();
    }

    /**
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @return mixed
     */
    public function getChoice() {
        return $this->config->allowUserToChooseAddress();
    }

    /**
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @return string
     */
    public function getInstructions() {
        if ($this->getChoice()) {
            return json_encode($this->config->getAddressValidationInstructionsWithChoice());
        } else {
            return json_encode($this->config->getAddressValidationInstructionsWithOutChoice());
        }
    }

    /**
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @return string
     */
    public function getErrorInstructions() {
        return json_encode($this->config->getAddressValidationErrorInstructions());
    }

    /**
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @return mixed
     */
    public function getCountriesEnabled() {
        return $this->config->getAddressValidationCountriesEnabled();
    }

    /**
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @return string
     */
    public function getBaseUrl() {
        return $this->urlBuilder->getUrl(self::VALIDATE_ADDRESS_PATH);
    }
}