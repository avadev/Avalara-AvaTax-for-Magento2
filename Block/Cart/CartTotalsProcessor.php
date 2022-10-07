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

namespace ClassyLlama\AvaTax\Block\Cart;

use Magento\Checkout\Model\Layout\AbstractTotalsProcessor;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Store\Model\ScopeInterface;
use ClassyLlama\AvaTax\Helper\Config;

class CartTotalsProcessor extends AbstractTotalsProcessor implements LayoutProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $taxIncluded = (boolean)$this->scopeConfig->getValue(Config::XML_PATH_AVATAX_TAX_INCLUDED, ScopeInterface::SCOPE_STORES);
        if ($taxIncluded)
            $jsLayout['components']['block-totals']['children']['tax']['config']['title'] .= " (".__(Config::XML_SUFFIX_AVATAX_TAX_INCLUDED).")";
        return $jsLayout;
    }
}