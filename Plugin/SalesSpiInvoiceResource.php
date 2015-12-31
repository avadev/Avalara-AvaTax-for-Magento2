<?php

namespace ClassyLlama\AvaTax\Plugin;

use ClassyLlama\AvaTax\Model\Config;

class SalesSpiInvoiceResource
{
    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var \ClassyLlama\AvaTax\Model\InvoiceRepository
     */
    protected $avaTaxInvoiceRepository;

    /**
     * @param Config $avaTaxConfig
     * @param \ClassyLlama\AvaTax\Model\InvoiceRepository $avaTaxInvoiceRepository
     */
    public function __construct(
        Config $avaTaxConfig,
        \ClassyLlama\AvaTax\Model\InvoiceRepository $avaTaxInvoiceRepository
    ) {
        $this->avaTaxConfig = $avaTaxConfig;
        $this->avaTaxInvoiceRepository = $avaTaxInvoiceRepository;
    }

    /**
     * Plugin for
     *
     * @param \Magento\Sales\Model\Spi\InvoiceResourceInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\Model\AbstractModel $invoice
     * @return \Magento\Sales\Model\Spi\InvoiceResourceInterface
     */
    public function aroundSave(
        \Magento\Sales\Model\Spi\InvoiceResourceInterface $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $invoice
    ) {
        /** @var \Magento\Sales\Api\Data\InvoiceInterface $invoice */

        /** @var \Magento\Sales\Model\Spi\InvoiceResourceInterface $resultInvoice */
        $resultInvoice = $proceed($invoice);




        // Save AvaTax Invoice extension attributes

        // TODO: Check config to see if this should be enabled
        if (true == false)
        {
            return $resultInvoice;
        }

        // check to see if any extension attributes exist
        /* @var \Magento\Sales\Api\Data\InvoiceExtension $extensionAttributes */
        $extensionAttributes = $invoice->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return $resultInvoice;
        }

        // check to see if any values are set on the avatax extension attributes
        $avataxInvoice = $extensionAttributes->getAvataxExtension();
        if ($avataxInvoice == null) {
            return $resultInvoice;
        }

        // save the AvaTax Invoice
        $this->avaTaxInvoiceRepository->save($avataxInvoice);

        return $resultInvoice;
    }
}