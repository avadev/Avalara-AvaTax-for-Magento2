<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use \Magento\Framework\DataObject;
use \Magento\Framework\AppInterface as MageAppInterface;
use \ClassyLlama\AvaTax\Framework\AppInterface as AvaTaxAppInterface;


abstract class InteractionAbstract extends DataObject
{
    const API_URL_DEV = 'https://development.avalara.net';
    const API_URL_PROD = 'https://avatax.avalara.net';

    const API_PROFILE_NAME_DEV = 'Development';
    const API_PROFILE_NAME_PROD = 'Production';

    const API_APP_NAME_PREFIX = 'Magento';

    /**
     * @var ATConfigFactory
     */
    protected $avaTaxConfigFactory = null;

    public function __construct(
        ATConfigFactory $avaTaxConfigFactory
    ) {
        $this->avaTaxConfigFactory = $avaTaxConfigFactory;
    }

    protected function createAvaTaxProfile()
    {
        $this->avaTaxConfigFactory->create(
            [
                'name' => self::API_PROFILE_NAME_DEV,
                'values' => [
                    'url'       => self::API_URL_DEV,
                    'account'   => '1100000000',
                    'license'   => '1A2B3C4D5E6F7G8H',
                    'trace'     => true,
                    'client' => 'AvaTaxSample',
                    'name' => '14.4',
                ],
            ]
        );

        $this->avaTaxConfigFactory->create(
            [
                'name' => self::API_PROFILE_NAME_PROD,
                'values' => [
                    'url'       => self::API_URL_PROD,
                    'account'   => '<Your Production Account Here>',
                    'license'   => '<Your Production License Key Here>',
                    'trace'     => false,
                    'client' => 'AvaTaxSample',
                    'name' => '14.4',
                ],
            ]
        );
    }

    protected function generateClientName()
    {
        return self::API_APP_NAME_PREFIX . ' - ' . MageAppInterface::VERSION . ' - ' . AvaTaxAppInterface::APP_VERSION;
    }
}