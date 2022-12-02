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

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * @codeCoverageIgnore
 */
class AvaTax extends ExpandedFieldSet
{
    /**
     * @return string
     */
    public function getConfigSearchParamsJson()
    {
        $params = [];
        if ($this->getRequest()->getParam('section')) {
            $params['section'] = $this->getRequest()->getParam('section');
        }
        if ($this->getRequest()->getParam('expanded')) {
            $params['group'] = $this->getRequest()->getParam('expanded');
        }
        if ($this->getRequest()->getParam('field')) {
            $params['field'] = $this->getRequest()->getParam('field');
        }
        return json_encode($params);
    }
    
    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element)
    {
        $params = $this->getConfigSearchParamsJson();
        $html = parent::render($element);
        $html .= "<script>
                        require([
                            'jquery',
                            'uiRegistry',
                            'Magento_Ui/js/modal/confirm',
                            'mage/translate',
                            'mage/mage',
                            'prototype',
                            'mage/adminhtml/form',
                            'domReady!',
                            'jquery/ui'
                        ], function (jQuery) {
                            var previousTimerOfScroll = false;
                            function navigateToElementAndScroll(searchRequest, count) {
                                try {
                                    if ('section' in searchRequest) {
                                        var section = searchRequest.section;
                                    }
                                    if ('group' in searchRequest) {
                                        var group = searchRequest.group;
                                    }
                                    if ('field' in searchRequest) {
                                        var field = searchRequest.field;
                                    }
                                    if (typeof section === 'undefined') {
                                        return;
                                    }
                                    if (typeof group !== 'undefined') {
                                        var groupElementSelector = '#' + group;
                                        var groupElement = jQuery(groupElementSelector);
          
                                        var headerHeight = jQuery('.page-main-actions').offset().top;
                                        var scrollToTop =  groupElement.offset().top - headerHeight;
                                        jQuery('html, body').animate({
                                            scrollTop: scrollToTop
                                        }, 1000);

                                        if (Math.abs(jQuery(window).scrollTop() - scrollToTop) > 50 && count < 3) {
                                            count = count + 1;
                                            clearTimeout(previousTimerOfScroll);
                                            previousTimerOfScroll = setTimeout(function(){
                                                navigateToElementAndScroll(searchRequest, count);
                                            },100);
                                        } else {
                                            clearTimeout(previousTimerOfScroll);
                                        }                                       
                                    }
                                } catch (e) {
                                    console.log(e);
                                }                                    
                            }
                            try {
                                navigateToElementAndScroll(".$params.", 1);
                            } catch (e) {
                                console.log(e);
                            } 
                        });
                    </script>";

        return $html;
    }
}
