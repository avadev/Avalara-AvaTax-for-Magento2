<?php

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Block\CustomerAddress;
use Magento\Checkout\Model\ConfigProviderInterface;

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
     * BillingAddressValidationConfigProvider constructor.
     *
     * @param CustomerAddress $customerAddress
     */
    public function __construct(CustomerAddress $customerAddress)
    {
        $this->customerAddress = $customerAddress;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $config['billingAddressValidation'] = [
            'validationEnabled' => $this->customerAddress->isValidationEnabled(),
            'hasChoice'         => $this->customerAddress->getChoice(),
            'instructions'      => json_decode($this->customerAddress->getInstructions()),
            'errorInstructions' => json_decode($this->customerAddress->getErrorInstructions()),
            'countriesEnabled'  => $this->customerAddress->getCountriesEnabled(),
        ];

        return $config;
    }
}
