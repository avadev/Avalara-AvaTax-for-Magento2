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

/**
 * @method setCertificates(DataObject[] $certificates)
 * @method DataObject[] getCertificates()
 */
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
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $configResourceModel;

    /**
     * @param \Magento\Framework\Registry                $coreRegistry
     * @param Template\Context                           $context
     * @param Customer                                   $customerRest
     * @param DataObjectFactory                          $dataObjectFactory
     * @param UrlSigner                                  $urlSigner
     * @param \Magento\Framework\AuthorizationInterface  $authorization
     * @param \Magento\Config\Model\ResourceModel\Config $configResourceModel
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        Template\Context $context,
        Customer $customerRest,
        DataObjectFactory $dataObjectFactory,
        UrlSigner $urlSigner,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Config\Model\ResourceModel\Config $configResourceModel,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->coreRegistry = $coreRegistry;
        $this->customerRest = $customerRest;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->urlSigner = $urlSigner;
        $this->authorization = $authorization;

        $this->prepareData();
        $this->configResourceModel = $configResourceModel;
    }

    protected function prepareData()
    {
        $certificates = [];

        try {
            $certificates = $this->customerRest->getCertificatesList(
                $this->dataObjectFactory->create(
                    [
                        'data' => [
                            'customer_id' => $this->coreRegistry->registry(
                                RegistryConstants::CURRENT_CUSTOMER_ID
                            )
                        ]
                    ]
                )
            );
        } catch (AvataxConnectionException $e) {
        }

        $this->setCertificates($certificates);
    }

    /**
     * Return Tab label
     * @return string
     */
    public function getTabLabel()
    {
        return __('Tax Certificates');
    }

    /**
     * Return Tab title
     * @return string
     */
    public function getTabTitle()
    {
        return __('Tax Certificates');
    }

    /**
     * Tab class getter
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Can show tab in tabs
     * @return boolean
     */
    public function canShowTab()
    {
        return $this->coreRegistry->registry(
                RegistryConstants::CURRENT_CUSTOMER_ID
            ) !== null && $this->authorization->isAllowed(self::CERTIFICATES_RESOURCE);
    }

    /**
     * Tab is hidden
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    public function shouldShowWarning()
    {
        $connection = $this->configResourceModel->getConnection();
        $select = $connection->select()->from($this->configResourceModel->getMainTable(), 'count(*) as count')->oRwhere(
                'path = ?',
                \ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_DEVELOPMENT_COMPANY_CODE
            )->oRwhere('path = ?', \ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_PRODUCTION_COMPANY_CODE);

        return (int)$connection->fetchOne($select) > 2;
    }

    public function getCertificateUrl($certificateId)
    {
        $parameters = [
            'certificate_id' => $certificateId,
            'customer_id' => $this->coreRegistry->registry(
                RegistryConstants::CURRENT_CUSTOMER_ID
            ),
            'expires' => time() + (60 * 60 * 24) // 24 hour access
        ];

        $parameters['signature'] = $this->urlSigner->signParameters($parameters);
        // This messes with URL signing as the parameter is added after the fact. Don't use url keys for certificate downloads
        $parameters['_nosecret'] = true;

        return $this->getUrl('avatax/certificates/download', $parameters);
    }
}