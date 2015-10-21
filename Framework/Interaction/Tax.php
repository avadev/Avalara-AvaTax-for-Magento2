<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use AvaTax\TaxServiceSoap;
use AvaTax\TaxServiceSoapFactory;
use ClassyLlama\AvaTax\Model\Config;

class Tax extends InteractionAbstract
{
    /**
     * @var Address
     */
    protected $address = null;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var TaxServiceSoapFactory
     */
    protected $taxServiceSoapFactory = [];

    /**
     * @var TaxServiceSoap[]
     */
    protected $taxServiceSoap = [];

    public function __construct(
        Address $address,
        Config $config,
        TaxServiceSoapFactory $taxServiceSoapFactory
    ) {
        $this->address = $address;
        $this->config = $config;
        $this->taxServiceSoapFactory = $taxServiceSoapFactory;
    }

    /**
     * Get address service by type and cache instances by type to avoid duplicate instantiation
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param string $type
     * @return AddressServiceSoap
     */
    public function getTaxService($type = null)
    {
        if (is_null($type)) {
            $type = $this->config->getLiveMode() ? self::API_PROFILE_NAME_PROD : self::API_PROFILE_NAME_DEV;
        }
        if (!isset($this->taxServiceSoap[$type])) {
            $this->taxServiceSoap[$type] =
                $this->taxServiceSoapFactory->create(['configurationName' => $type]);
        }
        return $this->taxServiceSoap[$type];
    }
}