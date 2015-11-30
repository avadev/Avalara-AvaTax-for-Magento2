<?php

namespace ClassyLlama\AvaTax\Block\Checkout;

use ClassyLlama\AvaTax\Model\Config;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{

    const COMPONENT_PATH = 'ClassyLlama_AvaTax/js/view/ReviewPayment';

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * LayoutProcessor constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Overrides payment component and adds config variable to be used in the component and template
     *
     * This class takes the place of a layout config change to checkout_index_index.xml. Making the changes to the
     * layout this way is necessecary because the in the process of merging the layout files, layout overrides are
     * applied over existing nodes in alphabetical order by Namespace_ModuleName. So Magento_Checkout overrides
     * ClassyLlama_AvaTax because Magento_Checkout layout files are merged after ClassyLlama_AvaTax layout files. The
     * solution is to set the value of the converted object after the layout files have been merged. Additionally,
     * because the config fields must be accessed from PHP, the most efficient method of setting the config node values
     * is with PHP as the following code does.
     *
     * @author Nathan Toombs <nathan.toombs@classyllama.com>
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout) {
        if ($this->config->isModuleEnabled()) {
            if ($this->config->isAddressValidationEnabled()) {
                $userHasChoice = $this->config->allowUserToChooseAddress();
                if ($userHasChoice) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['config']['instructions'] = $this->config->getAddressValidationInstructionsWithChoice();
                } else {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['config']['instructions'] = $this->config->getAddressValidationInstructionsWithoutChoice();
                }
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['config']['choice'] = $userHasChoice;
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['component'] = self::COMPONENT_PATH;
            }
        }

        return $jsLayout;
    }
}
