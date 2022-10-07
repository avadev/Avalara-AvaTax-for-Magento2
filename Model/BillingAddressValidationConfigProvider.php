<?php

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Block\CustomerAddress;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class BillingAddressValidationConfigProvider
 *
 * @package ClassyLlama\AvaTax\Model
 */
class BillingAddressValidationConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CustomerAddress
     */
    private $customerAddress;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * BillingAddressValidationConfigProvider constructor.
     *
     * @param CustomerAddress $customerAddress
     * @param Json $serializer
     */
    public function __construct(CustomerAddress $customerAddress, Json $serializer)
    {
        $this->customerAddress = $customerAddress;
        $this->serializer = $serializer;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $config['billingAddressValidation'] = [
            'validationEnabled' => (bool)$this->customerAddress->isValidationEnabled(),
            'hasChoice'         => $this->customerAddress->getChoice(),
            'instructions'      => $this->serializer->unserialize($this->customerAddress->getInstructions()),
            'errorInstructions' => $this->serializer->unserialize($this->customerAddress->getErrorInstructions()),
            'countriesEnabled'  => $this->customerAddress->getCountriesEnabled() ?? "",
        ];

        return $config;
    }
}
