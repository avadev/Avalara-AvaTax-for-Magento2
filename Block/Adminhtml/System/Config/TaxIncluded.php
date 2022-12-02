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
class TaxIncluded extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $allowedOptions = \ClassyLlama\AvaTax\Model\Config\Source\TaxIncluded::allowedOptions();
        $jsHtml = ''; 
        if (in_array($element->getValue(), $allowedOptions)) {
            foreach ($allowedOptions as $key => $value) {
               $jsHtml .= "jQuery('#tax_avatax_configuration_sales_tax_tax_included".$key."').prop('disabled',true); ";
            }
        }        
        $html = $element->getElementHtml();
        
        $html .= "<script>
                        require([
                            'jquery',
                            'Magento_Ui/js/modal/confirm'
                        ], function(jQuery, confirmation) {
                            setTimeout(function(){ ".$jsHtml." }, 300);
                            jQuery('#tax_avatax_configuration_sales_tax_tax_included0,#tax_avatax_configuration_sales_tax_tax_included1').on('click', function(event){
                                event.preventDefault;
                                    confirmation({
                                title: 'Warning!!!',
                                content: 'For tax compliance reasons, once you save the settings for the first time, you won\'t be able to change the Taxation Policy again.',
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
        return $html;
    }
}
