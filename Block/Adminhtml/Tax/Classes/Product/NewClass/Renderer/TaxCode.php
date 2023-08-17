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

namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Product\NewClass\Renderer;

/**
 * Tax Code field type Renderer.
 */
class TaxCode extends \Magento\Framework\Data\Form\Element\AbstractElement
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
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\UrlInterface $urlBuilder,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
    }

    /**
     * Return element html code
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = parent::getElementHtml();
        $html .= '<script type="text/x-magento-init">
        {
            "#avatax_code": {
                "suggest": {
                    "dropdownWrapper" : "<div class=\\"autocomplete-results\\"></div >",
                    "template" : "[data-template=taxcode-search-suggest]",
                    "source" : "'.$this->urlBuilder->getUrl('avatax/tax_classes/search').'",
                    "termAjaxArgument" : "query",
                    "filterProperty" : "name",
                    "preventClickPropagation" : false,
                    "minLength" : 2,
                    "submitInputOnEnter" : false,
					"valueField" : true
                }
            }
        }
        </script>';
        $html .= $this->getSuggestTemplate();
        return $html;
    }

    /**
     * Return data template html code
     *
     * @return string
     */
    public function getSuggestTemplate()
    {
        $templateHtml = 
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
            </script>
            ';
        
        return $templateHtml;
    }
}
