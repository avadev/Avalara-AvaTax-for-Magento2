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
namespace ClassyLlama\AvaTax\Block\Adminhtml\System\Config;
/**
 * @codeCoverageIgnore
 */
class CommonJs extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $configHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \ClassyLlama\AvaTax\Helper\Config $configHelper,
        array $data = []
    ){
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->getElementHtml();
        if($this->configHelper->isProductSyncEnabled() == '1')
        {
            $html .= "<script>
            require([
                'jquery',
                'Magento_Ui/js/modal/confirm',
                'domReady!',
                'jquery/ui',
                'uiRegistry'
            ], function(jQuery, confirmation) {
                jQuery('#tax_avatax_configuration_extension_mode_development_company_id,#tax_avatax_configuration_extension_mode_production_company_id').on('change', function(event){
                    event.preventDefault;
                        confirmation({
                    title: 'Warning!!!',
                    content: 'Product sync to the current AvaTax company account will stop and start for the new company account.',
                    actions: {
                        confirm: function(){},
                        cancel: function(){
                            return false;
                        },
                        always: function(){}
                    }
                    });
                });
                });
            </script>";
        }
        return $html;
    }
}
