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

use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Helper\DocumentManagementConfig;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;

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
     * @var \ClassyLlama\AvaTax\Api\RestCustomerInterface
     */
    protected $customerRest;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param Config                                        $config
     * @param DocumentManagementConfig                      $documentManagementConfig
     * @param \Magento\Framework\UrlInterface               $urlBuilder
     * @param \ClassyLlama\AvaTax\Api\RestCustomerInterface $customerRest
     * @param DataObjectFactory                             $dataObjectFactory
     * @param \Magento\Customer\Model\Session               $customerSession
     */
    public function __construct(
        Config $config,
        DocumentManagementConfig $documentManagementConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        \ClassyLlama\AvaTax\Api\RestCustomerInterface $customerRest,
        DataObjectFactory $dataObjectFactory,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->documentManagementConfig = $documentManagementConfig;
        $this->customerRest = $customerRest;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * Adds certificate management links to checkout
     *
     * @param array $jsLayout
     *
     * @return array
     * @throws AvataxConnectionException
     */
    public function process($jsLayout)
    {
        if ($this->config->isModuleEnabled() && $this->documentManagementConfig->isEnabled()) {
            $newCertText = \count($this->getCertificates()) > 0 ? __(
                $this->documentManagementConfig->getCheckoutLinkTextNewCertCertsExist()
            ) : __($this->documentManagementConfig->getCheckoutLinkTextNewCertNoCertsExist());

            $config = [
                'certificatesLink' => $this->urlBuilder->getUrl('avatax/certificates'),
                'newCertText' => $newCertText,
                'manageCertsText' => __($this->documentManagementConfig->getCheckoutLinkTextManageExistingCert()),
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

    /**
     * @return DataObject[]
     * @throws AvataxConnectionException
     */
    public function getCertificates()
    {
        $certificates = [];
        $customerId = $this->customerSession->getCustomer()->getId();

        if ($customerId === null) {
            return $certificates;
        }

        $certificates = $this->customerRest->getCertificatesList(
            $this->dataObjectFactory->create(['data' => ['customer_id' => $customerId]])
        );

        return $certificates;
    }
}
