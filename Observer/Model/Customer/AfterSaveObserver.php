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

namespace ClassyLlama\AvaTax\Observer\Model\Customer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AfterSaveObserver implements ObserverInterface
{
    /**
     * @var \ClassyLlama\AvaTax\Api\RestCustomerInterface
     */
    protected $restCustomerInterface;
    /**
     * @var \ClassyLlama\AvaTax\Helper\DocumentManagementConfig
     */
    protected $documentManagementConfig;

    /**
     * BeforeSaveObserver constructor.
     *
     * @param \ClassyLlama\AvaTax\Api\RestCustomerInterface $restCustomerInterface
     * @param \ClassyLlama\AvaTax\Helper\DocumentManagementConfig $documentManagementConfig
     */
    public function __construct(
        \ClassyLlama\AvaTax\Api\RestCustomerInterface $restCustomerInterface,
        \ClassyLlama\AvaTax\Helper\DocumentManagementConfig $documentManagementConfig
    )
    {
        $this->restCustomerInterface = $restCustomerInterface;
        $this->documentManagementConfig = $documentManagementConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $observer->getData('customer_data_object');

        if ($this->documentManagementConfig->isEnabled($customer->getStoreId())) {

            try {
                $this->restCustomerInterface->updateCustomer($customer, null, $customer->getStoreId());
            } catch (\Exception $e) {
                //todo what happens if update fails?
            }
        }

    }
}
