<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\Address;

use ClassyLlama\AvaTax\Framework\Interaction\InteractionAbstract;
use ClassyLlama\AvaTax\Model\Config;
use AvaTax\ATConfigFactory;
use AvaTax\AddressFactory;
use AvaTax\AddressServiceSoapFactory;

class AddressAbstract extends InteractionAbstract
{
    /**
     * @var AddressFactory
     */
    protected $addressFactory = null;

    /**
     * @var AddressServiceSoap
     */
    protected $addressServiceSoapFactory = null;

    /**
     * @param ATConfigFactory $avaTaxConfigFactory
     * @param Config $config
     * @param AddressFactory $addressFactory
     * @param AddressServiceSoapFactory $addressServiceSoapFactory
     */
    public function __construct(
        ATConfigFactory $avaTaxConfigFactory,
        Config $config,
        AddressFactory $addressFactory,
        AddressServiceSoapFactory $addressServiceSoapFactory
    ) {
        $this->addressFactory = $addressFactory;
        $this->addressServiceSoapFactory = $addressServiceSoapFactory;
        parent::__construct($avaTaxConfigFactory, $config);
    }
}