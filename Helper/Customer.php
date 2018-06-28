<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;

class Customer extends AbstractHelper
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Config                      $config
     * @param Context                     $context
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Config $config,
        Context $context
    )
    {
        parent::__construct( $context );

        $this->config = $config;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param int $customerId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    protected function getCustomerById( $customerId )
    {
        $customer = null;

        try
        {
            $customer = $this->customerRepository->getById( $customerId );
        }
        catch (\Magento\Framework\Exception\NoSuchEntityException $noSuchEntityException)
        {
        }
        catch (LocalizedException $localizedException)
        {
        }

        return $customer;
    }

    /**
     * This method will attempt to retrieve the provided customer code value as a system-defined customer attribute; if
     * that fails, then it will attempt to retrieve the value as a custom attribute
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string                                       $customerCode
     *
     * @return mixed
     */
    protected function retrieveCustomerCode( $customer, $customerCode )
    {
        // Convert provided customer code to getter name
        $getCustomerCode = 'get' . str_replace( '_', '', ucwords( $customerCode, '_' ) );
        if (method_exists( $customer, $getCustomerCode ))
        {
            // A method exists with this getter name, call it
            return $customer->{$getCustomerCode}();
        }
        // This was not a system-defined customer attribute, retrieve it as a custom attribute
        $attribute = $customer->getCustomAttribute( $customerCode );
        if (is_null( $attribute ))
        {
            // Retrieving the custom attribute failed, or no value was set, return null
            return null;
        }

        // Return value of custom attribute
        return $attribute->getValue();
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return string
     */
    public function getCustomerCodeFromNameId( $customer = null )
    {
        $name = Config::CUSTOMER_MISSING_NAME;
        $id = Config::CUSTOMER_GUEST_ID;

        if ($customer !== null && $customer->getId() !== null)
        {
            $name = "{$customer->getFirstname()} {$customer->getLastname()}";
            $id = $customer->getId();
        }

        return "{$name} ({$id})";
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string                                       $attribute
     *
     * @return mixed
     */
    public function getCustomerCodeFromAttribute( $customer, $attribute )
    {
        // Retrieve attribute value using provided attribute code
        $attributeValue = $this->retrieveCustomerCode( $customer, $attribute );

        if ($attributeValue === null && $attribute === Config::CUSTOMER_FORMAT_OPTION_EMAIL)
        {
            return Config::CUSTOMER_MISSING_EMAIL;
        }

        if ($attributeValue !== null && (is_string( $attributeValue ) || is_numeric( $attributeValue )))
        {
            // Customer has a value defined for provided attribute code and the provided value is a string
            return $attributeValue;
        }

        return null;
    }

    /**
     * Return customer code according to the admin configured format
     *
     * @param int      $customerId
     * @param int|null $guestId
     * @param int|null $storeId
     *
     * @return string
     */
    public function getCustomerCode( $customerId, $guestId = null, $storeId = null )
    {
        // Retrieve the customer code configuration value
        $customerCodeFormat = $this->config->getCustomerCodeFormat( $storeId );
        $customerCode = $customerId ?: strtolower( Config::CUSTOMER_GUEST_ID ) . "-{$guestId}";

        // This is the default value, ignore handling
        if ($customerCodeFormat === Config::CUSTOMER_FORMAT_OPTION_ID)
        {
            return $customerCode;
        }

        $customer = $this->getCustomerById( $customerId );

        // Customer code is the combination of the customer name and their Magento Customer ID
        if ($customerCodeFormat === Config::CUSTOMER_FORMAT_OPTION_NAME_ID)
        {
            return $this->getCustomerCodeFromNameId( $customer );
        }

        // Customer code is defined on a customer attribute
        if ($customerId !== null)
        {
            return $this->getCustomerCodeFromAttribute( $customer, $customerCodeFormat ) ?: $customerCode;
        }

        // This is a guest so no attribute value exists and neither does a customer ID
        return $customerCode;
    }
}