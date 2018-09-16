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
use ClassyLlama\AvaTax\Helper\DocumentManagementConfig;

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
     * @var DocumentManagementConfig
     */
    protected $documentManagementConfig;

    /**
     * @param Config                          $config
     * @param DocumentManagementConfig        $documentManagementConfig
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        Config $config,
        DocumentManagementConfig $documentManagementConfig,
        \Magento\Framework\UrlInterface $urlBuilder
    )
    {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->documentManagementConfig = $documentManagementConfig;
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
        if ($this->config->isModuleEnabled() && $this->documentManagementConfig->isEnabled()) {
            $config = [
                'certificatesLink' => $this->urlBuilder->getUrl('avatax/certificates'),
                'newCertNoneExist' => __($this->documentManagementConfig->getCheckoutLinkTextNewCertNoCertsExist()),
                'newCertExist' => __($this->documentManagementConfig->getCheckoutLinkTextNewCertCertsExist()),
                'manageCerts' => __($this->documentManagementConfig->getCheckoutLinkTextManageExistingCert()),
                'enabledCountries' => $this->documentManagementConfig->getEnabledCountries()
            ];

            // Set config for payments area
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["billing-step"]["children"]["payment"]["children"]["payments-list"]["config"] = array_merge(
                $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["billing-step"]["children"]["payment"]["children"]["payments-list"]["config"],
                $config
            );

            // Set config for tax summary area
            $jsLayout["components"]["checkout"]["children"]["sidebar"]["children"]["summary"]["children"]["totals"]["children"]["tax"]["config"] = array_merge(
                $jsLayout["components"]["checkout"]["children"]["sidebar"]["children"]["summary"]["children"]["totals"]["children"]["tax"]["config"],
                $config
            );
        }

        return $jsLayout;
    }
}
