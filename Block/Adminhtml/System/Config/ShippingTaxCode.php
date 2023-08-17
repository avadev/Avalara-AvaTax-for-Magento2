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

use Magento\Backend\Block\Template\Context;

/**
 * Provides auto lookup functionality for shipping tax code
 */
class ShippingTaxCode extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @param Context $context
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {     
        $html = $element->getElementHtml();

        $html .= '<script type="text/x-magento-init">
        {
            "#tax_avatax_configuration_sales_tax_shipping_tax_code": {
                "suggest": {
                    "dropdownWrapper" : "<div class=\\"autocomplete-results\\"></div >",
                    "template" : "[data-template=taxcode-search-suggest]",
                    "source" : "'.$this->urlBuilder->getUrl('avatax/tax_classes/search',['shipping_taxcode' => true]).'",
                    "termAjaxArgument" : "query",
                    "filterProperty" : "name",
                    "minLength" : 2,
                    "valueField" : true
                }
            }
        }
        </script>';

        $html .= 
            '<script data-template="taxcode-search-suggest" type="text/x-magento-template">
                <ul class="search-taxcode-menu" data-mage-init=\'{"ClassyLlama_AvaTax/js/tax-code-update": {}}\'>
                    <% if (data.items.length) { %>
                        <% _.each(data.items, function(value){ %>
                            <li class="item" data-tax-code="<%- value.code %>"
                            <%= data.optionData(value) %>
                            >
                            <span><a href="javascript:void(0);" class="title"><%- value.code %></a></span>
                            <span class="description"><%- value.description %></span>
                        </li>
                        <% }); %>
                    <% } else { %>
                        <li>
                            <span class="mage-suggest-no-records">
                                '.$this->escaper->escapeHtml(__('No records found.')) .'
                            </span>
                        </li>
                    <% } %>
                </ul>
            </script>';
        
        return $html;
    }
}
