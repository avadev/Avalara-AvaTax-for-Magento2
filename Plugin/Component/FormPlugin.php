<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Plugin\Component;

class FormPlugin
{
    /**
     * @var \ClassyLlama\AvaTax\Helper\DocumentManagementConfig
     */
    protected $documentManagementConfig;

    /**
     * @param \ClassyLlama\AvaTax\Helper\DocumentManagementConfig $documentManagementConfig
     */
    public function __construct(\ClassyLlama\AvaTax\Helper\DocumentManagementConfig $documentManagementConfig)
    {
        $this->documentManagementConfig = $documentManagementConfig;
    }

    /**
     * @param \Magento\Ui\Component\Form $subject
     * @param array                      $result
     *
     * @return mixed
     */
    public function afterGetChildComponents(\Magento\Ui\Component\Form $subject, $result)
    {
        if (isset($result['customer_tax_certificates']) && !$this->documentManagementConfig->isEnabled()) {
            unset($result['customer_tax_certificates']);
        }

        return $result;
    }
}
