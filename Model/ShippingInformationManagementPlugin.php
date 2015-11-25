<?php

namespace ClassyLlama\AvaTax\Model;

use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformation;
use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation as ValidationInteraction;
use \Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionFactory;

class ShippingInformationManagementPlugin
{
    /**
     * @var ValidationInteraction
     */
    protected $validationInteraction = null;

    protected $shippingInformation = null;

    protected $quoteRepository = null;

    /**
     * @var PaymentDetailsExtensionFactory|null
     */
    protected $shippingInformationExtensionFactory = null;

    public function __construct(
        ValidationInteraction $validationInteraction,
        ShippingInformation $shippingInformation,
        CartRepositoryInterface $quoteRepository,
        PaymentDetailsExtensionFactory $shippingInformationExtensionFactory
    ) {
        $this->validationInteraction = $validationInteraction;
        $this->shippingInformation = $shippingInformation;
        $this->quoteRepository = $quoteRepository;
        $this->shippingInformationExtensionFactory = $shippingInformationExtensionFactory;
    }

    public function aroundSaveAddressInformation(
        ShippingInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
//        $quote = $this->quoteRepository->getActive($cartId);
        $shippingAddress = $addressInformation->getShippingAddress();
        $street = $shippingAddress->getStreet();
        $address = [
            'line1'         => array_key_exists(0, $street)?$street[0]:'',
            'line2'         => array_key_exists(1, $street)?$street[1]:'',
            'line3'         => array_key_exists(2, $street)?$street[2]:'',
            'city'          => $shippingAddress->getCity(),
            'region'        => $shippingAddress->getRegion(),
            'postalCode'    => $shippingAddress->getPostcode(),
            'country'       => $shippingAddress->getCountryId()
        ];

        $valid = $this->validationInteraction->validateAddress($address);

        $returnValue = $proceed($cartId, $addressInformation);

        $shippingInformationExtension = $this->shippingInformationExtensionFactory->create();

        $shippingInformationExtension->setData('foo', 'bar');

//        $extensibleAttribute =  $productOption->getExtensionAttributes()
//            ? $productOption->getExtensionAttributes()
//            : $this->extensionFactory->create();
//        $attributes->setValidAddress($valid);
//        $this->shippingInformation->setExtensionAttributes($shippingInformationExtension);

        $returnValue->setExtensionAttributes($shippingInformationExtension);
        return $returnValue;
    }
}