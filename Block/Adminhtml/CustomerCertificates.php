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

namespace ClassyLlama\AvaTax\Block\Adminhtml;

use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Customer;
use ClassyLlama\AvaTax\Helper\UrlSigner;
use Magento\Backend\Block\Template;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;

class CustomerCertificates extends Template implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    protected $_template = 'ClassyLlama_AvaTax::customer-certificates.phtml';

    const CERTIFICATES_RESOURCE = 'ClassyLlama_AvaTax::customer_certificates';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var Customer
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
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var \ClassyLlama\AvaTax\Model\ResourceModel\Config
     */
    protected $configResourceModel;

    /**
     * @var DataObject[]
     */
    protected $certificates;

    /**
     * @param \Magento\Framework\Registry                    $coreRegistry
     * @param Template\Context                               $context
     * @param Customer                                       $customerRest
     * @param DataObjectFactory                              $dataObjectFactory
     * @param UrlSigner                                      $urlSigner
     * @param \Magento\Framework\AuthorizationInterface      $authorization
     * @param \ClassyLlama\AvaTax\Model\ResourceModel\Config $configResourceModel
     * @param array                                          $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        Template\Context $context,
        Customer $customerRest,
        DataObjectFactory $dataObjectFactory,
        UrlSigner $urlSigner,
        \Magento\Framework\AuthorizationInterface $authorization,
        \ClassyLlama\AvaTax\Model\ResourceModel\Config $configResourceModel,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->coreRegistry = $coreRegistry;
        $this->customerRest = $customerRest;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->urlSigner = $urlSigner;
        $this->authorization = $authorization;
        $this->configResourceModel = $configResourceModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Tax Certificates');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Tax Certificates');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return $this->getCustomerId() !== null && $this->authorization->isAllowed(self::CERTIFICATES_RESOURCE);
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return int
     */
    protected function getCustomerId()
    {
        return (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function shouldShowWarning()
    {
        return $this->configResourceModel->getConfigCount(
                [
                    \ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_DEVELOPMENT_COMPANY_CODE,
                    \ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_PRODUCTION_COMPANY_CODE
                ]
            ) > 2;
    }

    /**
     * @param $certificateId
     *
     * @return string
     */
    public function getCertificateUrl($certificateId)
    {
        $parameters = [
            'certificate_id' => $certificateId,
            'customer_id' => $this->getCustomerId(),
            'expires' => time() + (60 * 60 * 24) // 24 hour access
        ];

        $parameters['signature'] = $this->urlSigner->signParameters($parameters);
        // This messes with URL signing as the parameter is added after the fact. Don't use url keys for certificate downloads
        $parameters['_nosecret'] = true;

        return $this->getUrl('avatax/certificates/download', $parameters);
    }

    /**
     * @return DataObject[]
     */
    public function getCertificates()
    {
        if ($this->certificates !== null) {
            return $this->certificates;
        }

        $this->certificates = [];
        $customerId = $this->getCustomerId();

        if ($customerId === null) {
            return $this->certificates;
        }

        try {
            $this->certificates = $this->customerRest->getCertificatesList(
                $this->dataObjectFactory->create(['data' => ['customer_id' => $customerId]])
            );
        } catch (AvataxConnectionException $e) {
        }

        return $this->certificates;
    }
}