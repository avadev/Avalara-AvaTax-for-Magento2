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

namespace ClassyLlama\AvaTax\Plugin\View\Layout;

use ClassyLlama\AvaTax\Controller\Adminhtml\Certificates\Download;

class GenericPlugin
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(\Magento\Framework\AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Does the user have authorization to access the tax certificates for the customer
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->authorization->isAllowed(Download::CERTIFICATES_RESOURCE);
    }

    /**
     * If the tax certificates component in admin exists and the user isn't allowed to access it, hide it
     *
     * @param \Magento\Framework\View\Layout\Generic $subject
     * @param array                                  $configuration
     *
     * @return mixed
     */
    public function afterBuild(\Magento\Framework\View\Layout\Generic $subject, $configuration)
    {
        if (isset($configuration["components"]["customer_form"]["children"]["areas"]["children"]["customer_tax_certificates"]) && !$this->canShowTab(
            )) {
            unset($configuration["components"]["customer_form"]["children"]["areas"]["children"]["customer_tax_certificates"]);
        }

        return $configuration;
    }
}
