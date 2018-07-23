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

use Magento\Backend\Block\Template;

class CustomerCertificatesTab extends Template implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    const CERTIFICATES_RESOURCE = 'ClassyLlama_AvaTax::customer_certificates';

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param Template\Context                          $context
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->authorization = $authorization;
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
        $viewModel = $this->getData('view_model');

        if ($viewModel === null) {
            return false;
        }

        return $viewModel->getCustomerId() !== null && $this->authorization->isAllowed(self::CERTIFICATES_RESOURCE);
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}