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
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model\MultishippingCheckout;

use ClassyLlama\AvaTax\Api\Data\AddressInterface;
use ClassyLlama\AvaTax\Api\MultishippingAddressManagementInterface;
use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\Region;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\Quote\TotalsCollector;

class AddressManagement implements MultishippingAddressManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Region
     */
    private $region;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionInterfaceFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @var AddressFactory
     */
    private $quoteAddressFactory;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Region $region,
        AddressRepositoryInterface $addressRepository,
        RegionInterfaceFactory $regionInterfaceFactory,
        Session $customerSession,
        TotalsCollector $totalsCollector,
        AddressFactory $quoteAddressFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->region = $region;
        $this->addressRepository = $addressRepository;
        $this->regionInterfaceFactory = $regionInterfaceFactory;
        $this->customerSession = $customerSession;
        $this->totalsCollector = $totalsCollector;
        $this->quoteAddressFactory = $quoteAddressFactory;
    }

    /**
     * @param AddressInterface $address
     * @return bool
     */
    public function execute(AddressInterface $address): bool
    {
        try {
            $customerAddress = $this->addressRepository->getById($address->getCustomerAddressId());
            $customerAddress->setCity($address->getCity());
            $customerAddress->setPostcode($address->getPostcode());
            $customerAddress->setStreet([$address->getStreet()]);

            $customerRegion = $this->regionInterfaceFactory->create();
            $newRegion = $this->region->loadByName($address->getRegion(), $customerAddress->getCountryId());
            $customerRegion->setRegionCode($newRegion->getCode());
            $customerRegion->setRegion($newRegion->getName());
            $customerRegion->setRegionId($newRegion->getId());

            $customerAddress->setRegion($customerRegion);
            $customerAddress->setRegionId($customerRegion->getRegionId());

            $this->addressRepository->save($customerAddress);

            $quote = $this->quoteRepository->get($address->getQuoteId());
            if ($address->getAddressType() == \Magento\Sales\Model\Order\Address::TYPE_SHIPPING) {
                $this->updateQuoteCustomerShippingAddress($quote, $address->getCustomerAddressId());
            } else {
                $billingAddress = $this->quoteAddressFactory->create()->importCustomerAddressData($customerAddress);
                $quote->setBillingAddress($billingAddress);
                $this->quoteRepository->save($quote);
            }
        } catch (NoSuchEntityException $e) {
        } catch (LocalizedException $e) {
        }

        return true;
    }

    /**
     * @param $quote
     * @param $addressId
     * @return $this
     * @throws LocalizedException
     */
    private function updateQuoteCustomerShippingAddress($quote, $addressId): AddressManagement
    {
        if (!$this->isAddressIdApplicable($addressId)) {
            throw new LocalizedException(__('Verify the shipping address information and continue.'));
        }
        try {
            $address = $this->addressRepository->getById($addressId);
        } catch (Exception $e) {
            //
        }
        if (isset($address)) {
            $quoteAddress = $quote->getShippingAddressByCustomerAddressId($addressId);
            $quoteAddress->setCollectShippingRates(true)->importCustomerAddressData($address);
            $this->totalsCollector->collectAddressTotals($quote, $quoteAddress);
            $this->quoteRepository->save($quote);
        }

        return $this;
    }

    /**
     * @param $addressId
     * @return bool
     */
    private function isAddressIdApplicable($addressId): bool
    {
        $applicableAddressIds = array_map(function ($address) {
            /** @var \Magento\Customer\Api\Data\AddressInterface $address */
            return $address->getId();
        }, $this->getCustomer()->getAddresses());

        return !is_numeric($addressId) || in_array($addressId, $applicableAddressIds);
    }

    /**
     * @return CustomerInterface
     */
    private function getCustomer(): CustomerInterface
    {
        return $this->customerSession->getCustomerDataObject();
    }
}
