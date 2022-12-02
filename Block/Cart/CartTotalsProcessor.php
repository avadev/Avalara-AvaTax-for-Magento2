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
use Magento\Framework\App\Config\ScopeConfigInterface;

class CartTotalsProcessor extends AbstractTotalsProcessor implements LayoutProcessorInterface
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config
    ) {
        $this->config = $config;
        parent::__construct($scopeConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $taxIncluded = $this->config->getTaxationPolicy(null, ScopeInterface::SCOPE_STORES);
        if ($taxIncluded)
            $jsLayout['components']['block-totals']['children']['tax']['config']['title'] .= " (".__(Config::XML_SUFFIX_AVATAX_TAX_INCLUDED).")";
        return $jsLayout;
    }
}