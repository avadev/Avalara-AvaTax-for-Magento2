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

namespace ClassyLlama\AvaTax\Block\Checkout;

use ClassyLlama\AvaTax\Helper\Config;

class AddressValidationLayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @const Path to template
     */
    const COMPONENT_PATH = 'ClassyLlama_AvaTax/js/view/ReviewPayment';

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * AddressValidationLayoutProcessor constructor.
     * @param Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(Config $config, \Magento\Store\Model\StoreManagerInterface $storeManager) {
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Overrides payment component and adds config variable to be used in the component and template
     *
     * This class takes the place of a layout config change to checkout_index_index.xml. Making the changes to the
     * layout this way is necessary because in the process of merging the layout files, layout overrides are
     * applied over existing nodes in alphabetical order by Namespace_ModuleName. So Magento_Checkout overrides
     * ClassyLlama_AvaTax because Magento_Checkout layout files are merged after ClassyLlama_AvaTax layout files. The
     * solution is to set the value of the converted object after the layout files have been merged. Additionally,
     * because the config fields must be accessed from PHP, the most efficient method of setting the config node values
     * is with PHP as the following code does.
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout) {
        if ($this->config->isModuleEnabled()) {
            if ($this->config->isAddressValidationEnabled($this->storeManager->getStore())) {
                $userHasChoice = $this->config->allowUserToChooseAddress($this->storeManager->getStore());
                if ($userHasChoice) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                        ['payment']['config']['instructions']
                        = $this->config->getAddressValidationInstructionsWithChoice($this->storeManager->getStore());
                } else {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                        ['payment']['config']['instructions']
                        = $this->config->getAddressValidationInstructionsWithoutChoice($this->storeManager->getStore());
                }
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['config']['errorInstructions']
                    = $this->config->getAddressValidationErrorInstructions($this->storeManager->getStore());
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['config']['choice'] = $userHasChoice;
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['component'] = self::COMPONENT_PATH;
            }
        }
        return $jsLayout;
    }
}
