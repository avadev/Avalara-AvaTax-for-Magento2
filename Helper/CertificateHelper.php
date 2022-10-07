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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;

use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Helper\Config as AvataxConfig;

class CertificateHelper
{
    // 24 hours in seconds
    const CERTIFICATE_URL_EXPIRATION = (60 * 60 * 24);

    /**
     * @var array
     */
    protected $certificates = [];

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var UrlSigner
     */
    protected $urlSigner;

    /**
     * @var AvataxConfig
     */
    protected $avataxConfig;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \ClassyLlama\AvaTax\Api\RestCustomerInterface
     */
    protected $customerRest;

    /**
     * CertificateHelper constructor.
     *
     * @param DataObjectFactory                             $dataObjectFactory
     * @param \ClassyLlama\AvaTax\Api\RestCustomerInterface $customerRest
     * @param Config                                        $avataxConfig
     * @param \Magento\Framework\UrlInterface               $urlBuilder
     * @param UrlSigner                                     $urlSigner
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        \ClassyLlama\AvaTax\Api\RestCustomerInterface $customerRest,
        AvataxConfig $avataxConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        UrlSigner $urlSigner
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->urlSigner = $urlSigner;
        $this->avataxConfig = $avataxConfig;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerRest = $customerRest;
    }

    /**
     * Build url for certificate delete action.
     *
     * @param $certificateId
     * @param $customerId
     *
     * @return string
     */
    public function getCertificateDeleteUrl($certificateId, $customerId)
    {
        $params = [
            'certificate_id' => $certificateId,
            'customer_id' => $customerId
        ];

        return $this->urlBuilder->getUrl('avatax/certificates/delete', $params);
    }

    /**
     * @param $certificateId
     * @param $customerId
     *
     * @return string
     */
    public function getCertificateUrl($certificateId, $customerId)
    {
        $parameters = [
            'certificate_id' => $certificateId,
            'customer_id' => $customerId,
            // For security, expire the url after a period of time
            'expires' => time() + self::CERTIFICATE_URL_EXPIRATION
        ];

        $parameters['signature'] = $this->urlSigner->signParameters($parameters);
        // This messes with URL signing as the parameter is added after the fact. Don't use url keys for certificate downloads
        $parameters['_nosecret'] = true;

        return $this->urlBuilder->getUrl('avatax/certificates/download', $parameters);
    }

    /**
     * @param $customerId
     *
     * @return DataObject[]
     * @throws AvataxConnectionException
     */
    public function getCertificates($customerId)
    {
        if (isset($this->certificates[$customerId])) {
            return $this->certificates[$customerId];
        }

        $this->certificates[$customerId] = [];

        if ($customerId === null) {
            return [];
        }

        $this->certificates[$customerId] = $this->customerRest->getCertificatesList(
            $this->dataObjectFactory->create(['data' => ['customer_id' => $customerId]])
        );

        return $this->certificates[$customerId];
    }

    /**
     * Get Names of Certificate Status
     *
     * @return array
     */
    public function getCertificateStatusNames()
    {
        $certificateStatusNames = [
            'approved' => 'Approved',
            'denied'   => 'Denied',
            'pending'  => 'Pending'
        ];

        if (!$this->avataxConfig->getConfigData(Config::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CERTIFICATE_CUSTOM_STATUS_NAME)) {
            return $certificateStatusNames;
        }

        $approved = $this->avataxConfig->getConfigData(Config::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CERTIFICATE_NAME_STATUS_APPROVED);
        $denied = $this->avataxConfig->getConfigData(Config::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CERTIFICATE_NAME_STATUS_DENIED);
        $pending = $this->avataxConfig->getConfigData(Config::XML_PATH_AVATAX_DOCUMENT_MANAGEMENT_CERTIFICATE_NAME_STATUS_PENDING);

        if ($approved) {
            $certificateStatusNames['approved'] = $approved;
        };

        if ($denied) {
            $certificateStatusNames['denied'] = $denied;
        };

        if ($pending) {
            $certificateStatusNames['pending'] = $pending;
        };

        return $certificateStatusNames;
    }
}
