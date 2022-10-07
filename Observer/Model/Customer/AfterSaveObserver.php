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

use ClassyLlama\AvaTax\Api\RestCustomerInterface;
use ClassyLlama\AvaTax\Exception\AvaTaxCustomerDoesNotExistException;
use ClassyLlama\AvaTax\Helper\DocumentManagementConfig;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Exception;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Helper\Customer as CustomerHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\ScopeInterface;

class AfterSaveObserver implements ObserverInterface
{
    /**
     * @var RestCustomerInterface
     */
    protected $restCustomerInterface;

    /**
     * @var DocumentManagementConfig
     */
    protected $documentManagementConfig;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * BeforeSaveObserver constructor.
     *
     * @param RestCustomerInterface       $restCustomerInterface
     * @param DocumentManagementConfig $documentManagementConfig
     * @param State                        $appState
     * @param ManagerInterface         $messageManager
     * @param AvaTaxLogger       $avaTaxLogger
     * @param CacheInterface               $cache
     * @param CustomerHelper                                      $customerHelper
     */
    public function __construct(
        RestCustomerInterface $restCustomerInterface,
        DocumentManagementConfig $documentManagementConfig,
        State $appState,
        ManagerInterface $messageManager,
        AvaTaxLogger $avaTaxLogger,
        CacheInterface $cache,
        CustomerHelper $customerHelper
    )
    {
        $this->restCustomerInterface = $restCustomerInterface;
        $this->documentManagementConfig = $documentManagementConfig;
        $this->appState = $appState;
        $this->messageManager = $messageManager;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->cache = $cache;
        $this->customerHelper = $customerHelper;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var CustomerInterface $customer */
        $customer = $observer->getData('customer_data_object');

        if (!$customer || !$this->documentManagementConfig->isEnabled($customer->getStoreId())) {
            return;
        }

        try {
            $this->restCustomerInterface->updateCustomer($customer, null, $customer->getStoreId());
            $this->cache->clean([Config::AVATAX_CACHE_TAG,
             Config::AVATAX_CACHE_TAG . '-' .
             $this->customerHelper->getCustomerCode($customer, null, ScopeInterface::SCOPE_STORE)]);
        } catch (AvaTaxCustomerDoesNotExistException $avaTaxCustomerDoesNotExistException) {
            // Ignore errors where the customer doesn't exist
        } catch (Exception $exception) {
            if ($this->appState->getAreaCode() == FrontNameResolver::AREA_CODE) {
                //show error message
                $this->messageManager->addErrorMessage(__("Error sending updated customer data to Avalara."));
            }

            $this->avaTaxLogger->error(
                __("Error sending updated customer data to Avalara for customer %1.", $customer->getId()),
                ['error message' => $exception->getMessage()]
            );
        }

    }
}
