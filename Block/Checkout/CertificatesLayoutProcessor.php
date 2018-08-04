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

namespace ClassyLlama\AvaTax\Block\Checkout;

use ClassyLlama\AvaTax\Helper\Config;

class CertificatesLayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @const Path to template
     */
    const COMPONENT_PATH = 'ClassyLlama_AvaTax/js/view/ReviewPayment';

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Config                         $config
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(Config $config, \Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Adds certificate management links to checkout
     *
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout)
    {
        if ($this->config->isModuleEnabled()) {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["billing-step"]["children"]["payment"]["children"]["payments-list"]["config"]["certificatesLink"] = $this->urlBuilder->getUrl('avatax/certificates');
            $jsLayout["components"]["checkout"]["children"]["sidebar"]["children"]["summary"]["children"]["totals"]["children"]["tax"]["config"]["certificatesLink"] = $this->urlBuilder->getUrl('avatax/certificates');
        }

        return $jsLayout;
    }
}
