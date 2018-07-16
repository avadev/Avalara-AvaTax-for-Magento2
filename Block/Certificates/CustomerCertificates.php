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

namespace ClassyLlama\AvaTax\Block\Certificates;

use Magento\Framework\View\Element\Template;

class CustomerCertificates extends Template
{
    /**
     * @var \ClassyLlama\AvaTax\Block\Adminhtml\CustomerCertificates
     */
    protected $customerCertificatesBlock;

    /**
     * @param \ClassyLlama\AvaTax\Block\Adminhtml\CustomerCertificates $customerCertificatesBlock
     * @param Template\Context                                         $context
     * @param array                                                    $data
     */
    public function __construct(
        \ClassyLlama\AvaTax\Block\Adminhtml\CustomerCertificates $customerCertificatesBlock,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->customerCertificatesBlock = $customerCertificatesBlock;
    }

    public function getCertificates()
    {
        return $this->customerCertificatesBlock->getCertificates();
    }

    public function getCertificateUrl($certificateId)
    {
        return $this->customerCertificatesBlock->getCertificateUrl($certificateId);
    }
}