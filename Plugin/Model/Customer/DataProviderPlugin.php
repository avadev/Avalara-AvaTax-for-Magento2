<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Plugin\Model\Customer;

use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Helper\UrlSigner;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;

class DataProviderPlugin
{

    /**
     * @var \ClassyLlama\AvaTax\Api\RestCustomerInterface
     */
    protected $customerRest;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var UrlSigner
     */
    protected $urlSigner;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper
     */
    protected $certificateDeleteHelper;

    /**
     * @param \ClassyLlama\AvaTax\Api\RestCustomerInterface $customerRest
     * @param DataObjectFactory $dataObjectFactory
     * @param UrlSigner $urlSigner
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper $certificateDeleteHelper
     */
    public function __construct(
        \ClassyLlama\AvaTax\Api\RestCustomerInterface $customerRest,
        DataObjectFactory $dataObjectFactory,
        UrlSigner $urlSigner,
        \Magento\Framework\UrlInterface $urlBuilder,
        \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper $certificateDeleteHelper
    )
    {
        $this->customerRest = $customerRest;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->urlSigner = $urlSigner;
        $this->urlBuilder = $urlBuilder;
        $this->certificateDeleteHelper = $certificateDeleteHelper;
    }

    /**
     * @param $certificateId
     *
     * @param $customerId
     *
     * @return string
     */
    public function getCertificateUrl($certificateId, $customerId)
    {
        $parameters = [
            'certificate_id' => $certificateId,
            'customer_id' => $customerId,
            'expires' => time() + (60 * 60 * 24) // 24 hour access
        ];

        $parameters['signature'] = $this->urlSigner->signParameters($parameters);
        // This messes with URL signing as the parameter is added after the fact. Don't use url keys for certificate downloads
        $parameters['_nosecret'] = true;

        return $this->urlBuilder->getUrl('avatax/certificates/download', $parameters);
    }

    public function flattenDataObject($dataObject)
    {
        $data = $dataObject;

        if ($dataObject instanceof DataObject) {
            $data = $dataObject->getData();
        }

        if (!\is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $property) {
            $data[$key] = $this->flattenDataObject($property);
        }

        return $data;
    }

    /**
     * @param $customerId
     *
     * @return DataObject[]
     * @throws AvataxConnectionException
     */
    public function getCertificates($customerId)
    {
        $certificates = [];

        if ($customerId === null) {
            return $certificates;
        }

        $certificates = $this->customerRest->getCertificatesList(
            $this->dataObjectFactory->create(['data' => ['customer_id' => $customerId]])
        );

        foreach ($certificates as $certificate) {
            $certificate->setData(
                'certificate_url',
                $this->getCertificateUrl($certificate->getData('id'), $customerId)
            );

            $certificate->setData(
                'certificate_delete_url',
                $this->certificateDeleteHelper->getCertificateDeleteUrl($certificate->getData('id'), $customerId)
            );
        }

        return $certificates;
    }

    public function afterGetData(\Magento\Customer\Model\Customer\DataProvider $subject, $data)
    {
        if (empty($data)) {
            return $data;
        }

        foreach ($data as $index => $fieldData) {
            if (!isset($fieldData['customer']['entity_id'])) {
                continue;
            }

            $data[$index]['certificates'] = $this->flattenDataObject(
                $this->getCertificates($fieldData['customer']['entity_id'])
            );
        }

        return $data;
    }
}
