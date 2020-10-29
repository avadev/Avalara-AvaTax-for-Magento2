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
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Block;

use ClassyLlama\AvaTax\Helper\Config;
use Magento\Framework\View\Element\Template\Context;

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
     * CustomerAddress constructor
     * @param Context $context
     * @param array $data
     * @param Config $config
     */
    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getStoreCode() {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * @return mixed
     */
    public function isValidationEnabled() {
        return $this->config->isModuleEnabled($this->_storeManager->getStore())
            && $this->config->isAddressValidationEnabled($this->_storeManager->getStore());
    }

    /**
     * @return mixed
     */
    public function getChoice() {
        return $this->config->allowUserToChooseAddress($this->_storeManager->getStore());
    }

    /**
     * @return string
     */
    public function getInstructions() {
        if ($this->getChoice()) {
            return json_encode($this->config->getAddressValidationInstructionsWithChoice(
                $this->_storeManager->getStore()
            ));
        } else {
            return json_encode($this->config->getAddressValidationInstructionsWithOutChoice(
                $this->_storeManager->getStore()
            ));
        }
    }

    /**
     * @return string
     */
    public function getErrorInstructions() {
        return json_encode($this->config->getAddressValidationErrorInstructions($this->_storeManager->getStore()));
    }

    /**
     * @return mixed
     */
    public function getCountriesEnabled() {
        return $this->config->getAddressValidationCountriesEnabled($this->_storeManager->getStore());
    }

    /**
     * @return string
     */
    public function getBaseUrl() {
        return $this->_urlBuilder->getUrl(self::VALIDATE_ADDRESS_PATH);
    }
}
