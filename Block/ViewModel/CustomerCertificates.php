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

namespace ClassyLlama\AvaTax\Block\ViewModel;

use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Helper\UrlSigner;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;

class CustomerCertificates implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /** @var string  */
    const VALID_PENDING_STATUS = 'PENDING';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

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
     * @var \ClassyLlama\AvaTax\Model\ResourceModel\Config
     */
    protected $configResourceModel;

    /**
     * @var DataObject[]
     */
    protected $certificates;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper
     */
    protected $certificateDeleteHelper;

    /**
     * @var \ClassyLlama\AvaTax\Helper\CertificateHelper
     */
    protected $certificateHelper;

    /**
     * @param \Magento\Framework\Registry                        $coreRegistry
     * @param \ClassyLlama\AvaTax\Api\RestCustomerInterface      $customerRest
     * @param DataObjectFactory                                  $dataObjectFactory
     * @param UrlSigner                                          $urlSigner
     * @param \ClassyLlama\AvaTax\Model\ResourceModel\Config     $configResourceModel
     * @param \Magento\Framework\UrlInterface                    $urlBuilder
     * @param \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper $certificateDeleteHelper
     * @param \ClassyLlama\AvaTax\Helper\CertificateHelper       $certificateHelper
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \ClassyLlama\AvaTax\Api\RestCustomerInterface $customerRest,
        DataObjectFactory $dataObjectFactory,
        UrlSigner $urlSigner,
        \ClassyLlama\AvaTax\Model\ResourceModel\Config $configResourceModel,
        \Magento\Framework\UrlInterface $urlBuilder,
        \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper $certificateDeleteHelper,
        \ClassyLlama\AvaTax\Helper\CertificateHelper $certificateHelper
    )
    {
        $this->coreRegistry = $coreRegistry;
        $this->customerRest = $customerRest;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->urlSigner = $urlSigner;
        $this->configResourceModel = $configResourceModel;
        $this->urlBuilder = $urlBuilder;
        $this->certificateDeleteHelper = $certificateDeleteHelper;
        $this->certificateHelper = $certificateHelper;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @return bool
     */
    public function shouldShowWarning()
    {
        try {
            return $this->configResourceModel->getConfigCount(
                    [
                        \ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_DEVELOPMENT_COMPANY_CODE,
                        \ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_PRODUCTION_COMPANY_CODE
                    ]
                ) > 2;
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * @param $certificateId
     *
     * @return string
     */
    public function getCertificateUrl($certificateId)
    {
        return $this->certificateHelper->getCertificateUrl($certificateId, $this->getCustomerId());
    }

    /**
     * Build delete cert url
     *
     * @param int $certificateId
     *
     * @return string
     */
    public function getDeleteCertificateUrl(int $certificateId)
    {
        return $this->certificateHelper->getCertificateDeleteUrl($certificateId, $this->getCustomerId());
    }

    /**
     * @return DataObject[]
     * @throws AvataxConnectionException
     */
    public function getCertificates()
    {
        return $this->certificateHelper->getCertificates($this->getCustomerId());
    }

    /**
     * Certificate Status Names
     *
     * @return array
     */
    public function getCertificateStatusName()
    {
        return $this->certificateHelper->getCertificateStatusNames();
    }

    /**
     * Is Ready For Validation
     *
     * @param $status
     * @return bool
     */
    public function isReadyForValidation($status)
    {
        return $status === self::VALID_PENDING_STATUS;
    }
}
