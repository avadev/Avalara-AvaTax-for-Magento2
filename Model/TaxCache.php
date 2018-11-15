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

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Api\TaxCacheInterface;

class TaxCache implements TaxCacheInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \ClassyLlama\AvaTax\Helper\Customer
     */
    protected $customerHelper;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @param \ClassyLlama\AvaTax\Helper\Customer   $customerHelper
     * @param \Magento\Customer\Model\Session       $customerSession
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(
        \ClassyLlama\AvaTax\Helper\Customer $customerHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\CacheInterface $cache
    )
    {
        $this->customerSession = $customerSession;
        $this->customerHelper = $customerHelper;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        $customerId = $this->customerSession->getCustomerId();

        $this->clearCacheByCustomerId($customerId);
    }

    /**
     * @param $customerId
     */
    public function clearCacheByCustomerId($customerId)
    {
        $customerCode = $this->customerHelper->getCustomerCodeByCustomerId($customerId);

        $this->cache->clean([\ClassyLlama\AvaTax\Helper\Config::AVATAX_CACHE_TAG . "-{$customerCode}"]);
    }
}
