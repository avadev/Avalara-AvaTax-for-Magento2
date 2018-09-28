<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
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

    public function canShowTab()
    {
        return $this->authorization->isAllowed(Download::CERTIFICATES_RESOURCE);
    }

    public function afterBuild(\Magento\Framework\View\Layout\Generic $subject, $configuration)
    {
        if(isset($configuration["components"]["customer_form"]["children"]["areas"]["children"]["customer_tax_certificates"]) && !$this->canShowTab()) {
            unset($configuration["components"]["customer_form"]["children"]["areas"]["children"]["customer_tax_certificates"]);
        }

        return $configuration;
    }
}
