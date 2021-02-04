<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Helper\Multishipping\Checkout;

use ClassyLlama\AvaTax\Block\CustomerAddress;
use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation;
use ClassyLlama\AvaTax\Helper\Config;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AddressValidation
 *
 * @package ClassyLlama\AvaTax\Helper\Multishipping\Checkout
 */
class AddressValidation
{

    const AV_FIELDS
        = [
            AddressInterface::STREET,
            AddressInterface::CITY,
            AddressInterface::REGION,
            AddressInterface::POSTCODE,
        ];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Validation
     */
    private $validation;

    /**
     * @var CustomerAddress
     */
    private $customerAddressBlock;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        Validation $validation,
        CustomerAddress $customerAddress,
        SerializerInterface $serializer
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->validation = $validation;
        $this->customerAddressBlock = $customerAddress;
        $this->serializer = $serializer;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function isValidationEnabled()
    {
        return $this->config->isAddressValidationEnabled($this->storeManager->getStore());
    }

    /**
     * @param AddressInterface|Address $address
     * @return array
     * @throws AddressValidateException
     * @throws AvataxConnectionException
     * @throws LocalizedException
     */
    public function validateAddress(
        $address
    ) {
        $result = [];
        if (in_array($address->getCountryId(), explode(',', $this->customerAddressBlock->getCountriesEnabled()))) {
            /** @var AddressInterface $result */
            try {
                $validAddress = $this->validation->validateAddress($address,
                    $this->storeManager->getStore()->getId());
            } catch (\Exception $exception) {
                return [
                    'error'             => true,
                    'errorInstructions' => $exception->getMessage()
                ];
            }
            if ($validAddress) {
                $changedFields = $this->compareFields($address, $validAddress);
                $result = [
                    'error'               => false,
                    'isDifferent'         => !empty($changedFields),
                    'validAddress'        => $this->prepareAddressJson($validAddress),
                    'originalAddress'     => $this->prepareAddressJson($address),
                    'validAddressHtml'    => $this->prepareAddressString($validAddress, $changedFields),
                    'originalAddressHtml' => $this->prepareAddressString($address),
                    'hasChoice'           => $this->customerAddressBlock->getChoice(),
                    'instructions'        => json_decode($this->customerAddressBlock->getInstructions()),
                ];
            }
        }

        return $result;
    }

    /**
     * @param AddressInterface $originalAddress
     * @param AddressInterface $validAddress
     * @return array
     */
    private function compareFields($originalAddress, $validAddress)
    {
        $differentFields = [];
        foreach (self::AV_FIELDS as $field) {
            if ($originalAddress->getData($field) !== $validAddress->getData($field)) {
                $differentFields[] = $field;
            }
        }

        return $differentFields;
    }

    /**
     * @param AddressInterface $address
     * @param array $changedFields
     * @return string
     */
    private function prepareAddressString($address, $changedFields = [])
    {
        $string = $address->getFirstName() . " " . $address->getLastName() . "<br>";
        foreach (self::AV_FIELDS as $field) {
            if (in_array($field, $changedFields)) {
                $differentFields[] = $field;
                $string .= "<span class='address-field-changed'>" . $address->getData($field) . "</span><br>";
            } else {
                $string .= $address->getData($field) . "<br>";
            }
        }

        return $string;
    }

    /**
     * @param  $address
     * @return bool|string
     */
    private function prepareAddressJson($address)
    {
        $keys = array_merge([
            'address_id',
            'quote_id',
            'customer_id',
            'address_type',
            'customer_address_id',
        ], self::AV_FIELDS);
        $result = [];
        foreach ($keys as $field) {
            $result[$field] = $address->getData($field);
        }

        return $this->serializer->serialize($result);
    }
}
