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

namespace ClassyLlama\AvaTax\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;

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
        parent::__construct($context);

        $this->config = $config;
        $this->customerRepository = $customerRepository;
    }

    /**
     * This method will attempt to retrieve the provided customer code value as a system-defined customer attribute; if
     * that fails, then it will attempt to retrieve the value as a custom attribute
     *
     * @param CustomerInterface $customer
     * @param string            $customerCode
     *
     * @return string
     */
    protected function getAttributeValue($customer, $customerCode)
    {
        // Convert provided customer code to getter name
        $getCustomerCode = 'get' . str_replace('_', '', ucwords($customerCode, '_'));
        if (method_exists($customer, $getCustomerCode)) {
            // A method exists with this getter name, call it
            return $customer->{$getCustomerCode}();
        }
        // This was not a system-defined customer attribute, retrieve it as a custom attribute
        $attribute = $customer->getCustomAttribute($customerCode);
        if (is_null($attribute)) {
            // Retrieving the custom attribute failed, or no value was set, return null
            return '';
        }

        // Return value of custom attribute
        return (string)$attribute->getValue();
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return string
     */
    public function generateCustomerCodeFromNameId($customer = null)
    {
        $name = Config::CUSTOMER_MISSING_NAME;
        $id = Config::CUSTOMER_GUEST_ID;

        if ($customer !== null && $customer->getId() !== null) {
            $name = "{$customer->getFirstname()} {$customer->getLastname()}";
            $id = $customer->getId();
        }

        return "{$name} ({$id})";
    }

    /**
     * @param CustomerInterface $customer
     * @param string            $attribute
     *
     * @return string|null
     */
    public function generateCustomerCodeFromAttribute($customer, $attribute)
    {
        // Retrieve attribute value using provided attribute code
        $attributeValue = $this->getAttributeValue($customer, $attribute);

        if ($attributeValue === null && $attribute === Config::CUSTOMER_FORMAT_OPTION_EMAIL) {
            return Config::CUSTOMER_MISSING_EMAIL;
        }

        if ($attributeValue !== null && (is_string($attributeValue) || is_numeric($attributeValue))) {
            // Customer has a value defined for provided attribute code and the provided value is a string
            return (string)$attributeValue;
        }

        return null;
    }

    /**
     * Generate a new AvaTax customer code according to the admin configured format
     *
     * @param CustomerInterface|null $customer
     * @param int|null               $uniqueGuestIdentifier such as the quote or order ID
     * @param int|null               $storeId
     *
     * @return string
     */
    public function generateCustomerCode($customer, $uniqueGuestIdentifier = null, $storeId = null)
    {
        // Retrieve the customer code configuration value
        $customerCodeFormat = $this->config->getCustomerCodeFormat($storeId);
        $customerCode = $customer->getId() ?: strtolower(Config::CUSTOMER_GUEST_ID) . "-{$uniqueGuestIdentifier}";

        // This is the default value, ignore handling
        if ($customerCodeFormat === Config::CUSTOMER_FORMAT_OPTION_ID) {
            return $customerCode;
        }

        // Customer code is the combination of the customer name and their Magento Customer ID
        if ($customerCodeFormat === Config::CUSTOMER_FORMAT_OPTION_NAME_ID) {
            return $this->generateCustomerCodeFromNameId($customer);
        }

        // Customer code is defined on a customer attribute
        if ($customer !== null) {
            return $this->generateCustomerCodeFromAttribute($customer, $customerCodeFormat) ?: $customerCode;
        }

        // This is a guest so no attribute value exists and neither does a customer ID
        return $customerCode;
    }

    /**
     * Retrieve the AvaTax customer code from the customer model
     *
     * @param CustomerInterface|null $customer
     * @param int|null               $uniqueGuestIdentifier such as the quote or order ID
     * @param int|null               $storeId
     *
     * @return string
     * @throws LocalizedException
     */
    public function getCustomerCode($customer, $uniqueGuestIdentifier = null, $storeId = null)
    {
        $customerCode = null;

        // If the customer has a code stored on them, do not generate a new code
        if ($customer !== null) {
            $customerCode = $this->generateCustomerCodeFromAttribute(
                $customer,
                DocumentManagementConfig::AVATAX_CUSTOMER_CODE_ATTRIBUTE
            );

            if ($customerCode !== null && $customerCode !== '') {
                return $customerCode;
            }
        }

        $customerCode = $this->generateCustomerCode($customer, $uniqueGuestIdentifier, $storeId);

        // After generating a customer code, save it on the customer to ensure we always have the correct reference
        $customer->setCustomAttribute(DocumentManagementConfig::AVATAX_CUSTOMER_CODE_ATTRIBUTE, $customerCode);

        try {
            $this->customerRepository->save($customer);
        } catch (InputException $inputException) {
            throw new LocalizedException(__($inputException->getMessage()));
        } catch (InputMismatchException $inputMismatchException) {
            throw new LocalizedException(__($inputMismatchException->getMessage()));
        }

        return $customerCode;
    }

    /**
     * Retrieve the AvaTax customer code from a customer id
     *
     * @param int      $customerId
     * @param int|null $uniqueGuestIdentifier such as the quote or order ID
     * @param int|null $storeId
     *
     * @return string
     * @throws LocalizedException
     */
    public function getCustomerCodeByCustomerId($customerId, $uniqueGuestIdentifier = null, $storeId = null)
    {
        $customer = null;

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            // Don't need to handle this, we expect the possibility of null
        } catch (LocalizedException $e) {
            // Don't need to handle this, we expect the possibility of null
        }

        return $this->getCustomerCode($customer, $uniqueGuestIdentifier, $storeId);
    }
}
