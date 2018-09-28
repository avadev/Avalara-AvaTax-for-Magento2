<?php
/**
 * @category    ClassyLlama
 * @package
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Observer\Model\Customer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Website;

class BeforeSaveObserver implements ObserverInterface
{

    /**
     * @var Website
     */
    protected $websiteModel;

    /**
     * BeforeSaveObserver constructor.
     *
     * @param Website $websiteModel
     */
    public function __construct(Website $websiteModel)
    {
        $this->websiteModel = $websiteModel;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getData('customer');
        $websiteId = $customer->getWebsiteId();
        $website = $this->websiteModel->load($websiteId);
        $stores = $website->getStores();

//        die('We intercepted the customer save');
    }
}
