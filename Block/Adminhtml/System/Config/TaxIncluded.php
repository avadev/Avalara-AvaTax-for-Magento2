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
        if ($element->getValue() != -1) {
            $element->setReadonly(true);
        }
        $html = $element->getElementHtml();
        $html .= '<div class="confirmation-modal-taxation-policy" style="display: none;">';
        $html .= '<p>'.__('Warning! For tax compliance reasons, once you save the settings for the first time, you won\'t be able to change the Taxation Policy again.').'</p></div>';
        $html .= "<script>
                        require([
                            'jquery',
                            'Magento_Ui/js/modal/confirm'
                        ], function ($) {
                            'use strict';
                            $('#tax_avatax_general_tax_included').on('change', function() {
                                $('.confirmation-modal-taxation-policy').confirm({
                                    title: $.mage.__('Warning!'),
                                    actions: {
                                        confirm: function() {
                                            return true;
                                        }
                                    },
                                    buttons: [{
                                        text: $.mage.__('OK'),
                                        class: 'action primary action-accept',
                                        click: function (event) {
                                            this.closeModal(event, true);
                                        }
                                    }]
                                });
                            });
                        });
                    </script>";
        return $html;
    }
}
