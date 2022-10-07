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

namespace ClassyLlama\AvaTax\Plugin\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Config as QuoteConfig;
use ClassyLlama\AvaTax\Helper\CustomsConfig;

/**
 * Class Config
 */
class Config
{
    /**
     * @var \ClassyLlama\AvaTax\Helper\Config
     */
    protected $config = null;

    /**
     * @var CustomsConfig
     */
    protected $customsConfigHelper;

    /**
     * Config constructor.
     * @param \ClassyLlama\AvaTax\Helper\Config $config
     * @param CustomsConfig $customsConfigHelper
     */
    public function __construct(
        \ClassyLlama\AvaTax\Helper\Config $config,
        CustomsConfig $customsConfigHelper
    ) {
        $this->config = $config;
        $this->customsConfigHelper = $customsConfigHelper;
    }

    /**
     * Append attributes to the list of attributes loaded on the quote_items collection
     *
     * @param QuoteConfig $config
     * @param $attributes
     * @return array
     */
    public function afterGetProductAttributes(QuoteConfig $config, $attributes)
    {
        if ($this->config->getRef1Attribute()) {
            $attributes[] = $this->config->getRef1Attribute();
        }
        if ($this->config->getRef2Attribute()) {
            $attributes[] = $this->config->getRef2Attribute();
        }
        if ($this->config->getUpcAttribute()) {
            $attributes[] = $this->config->getUpcAttribute();
        }

        if ($this->customsConfigHelper->enabled()) {
            $attributes[] = 'avatax_cross_border_type';

            foreach ($this->customsConfigHelper->getUnitAmountAttributes() as $attrCode) {
                $attributes[] = $attrCode;
            }
        }

        return array_unique($attributes);
    }
}
