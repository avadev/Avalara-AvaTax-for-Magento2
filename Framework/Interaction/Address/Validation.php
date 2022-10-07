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

namespace ClassyLlama\AvaTax\Framework\Interaction\Address;

use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Framework\Interaction\Address;
use ClassyLlama\AvaTax\Api\RestAddressInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\DataObjectFactory;
use ClassyLlama\AvaTax\Helper\Rest\Config as RestConfig;

class Validation
{
    /**
     * @var Address
     */
    protected $interactionAddress;

    /**
     * @var RestAddressInterface
     */
    protected $addressService;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var RestConfig
     */
    protected $restConfig;

    /**
     * @param Address $interactionAddress
     * @param RestAddressInterface $addressService
     * @param DataObjectFactory $dataObjectFactory
     * @param RestConfig $restConfig
     */
    public function __construct(
        Address $interactionAddress,
        RestAddressInterface $addressService,
        DataObjectFactory $dataObjectFactory,
        RestConfig $restConfig
    ) {
        $this->interactionAddress = $interactionAddress;
        $this->addressService = $addressService;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->restConfig = $restConfig;
    }

    /**
     * Validate address using AvaTax Address Validation API
     *
     * @param array|\Magento\Customer\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|/AvaTax/ValidAddress|\Magento\Customer\Api\Data\AddressInterface|\Magento\Quote\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|array|null
     * @param $storeId
     * @return array|\Magento\Customer\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|/AvaTax/ValidAddress|\Magento\Customer\Api\Data\AddressInterface|\Magento\Quote\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|array|null
     * @throws AddressValidateException
     * @throws LocalizedException
     * @throws AvataxConnectionException
     */
    public function validateAddress($addressInput, $storeId)
    {
        $validateRequestData = [
            'address' => $this->interactionAddress->getAddress($addressInput),
            'text_case' => $this->restConfig->getTextCaseMixed(),
        ];
        $validateRequest = $this->dataObjectFactory->create(['data' => $validateRequestData]);
        $validateResult = $this->addressService->validate($validateRequest, null, $storeId);

        $validAddresses = ($validateResult->hasValidatedAddresses()) ? $validateResult->getValidatedAddresses() : null;
        if (is_null($validAddresses) || !is_array($validAddresses) || empty($validAddresses)) {
            return null;
        }

        $validAddress = array_shift($validAddresses);

        // Convert data back to the type it was passed in as
        switch (true) {
            case ($addressInput instanceof \Magento\Customer\Api\Data\AddressInterface):
                $validAddress = $this->interactionAddress
                    ->convertAvaTaxValidAddressToCustomerAddress($validAddress, $addressInput);
                break;
            case ($addressInput instanceof \Magento\Quote\Api\Data\AddressInterface):
                $validAddress = $this->interactionAddress
                    ->convertAvaTaxValidAddressToQuoteAddress($validAddress, $addressInput);
                break;
            default:
                throw new LocalizedException(__(
                    'Input parameter "$addressInput" was not of a recognized/valid type: "%1".',
                    [gettype($addressInput),]
                ));
                break;
        }

        return $validAddress;
    }
}
