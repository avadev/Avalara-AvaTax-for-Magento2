<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use Magento\Framework\DataObject;
use Magento\Framework\AppInterface as MageAppInterface;
use ClassyLlama\AvaTax\Framework\AppInterface as AvaTaxAppInterface;
use ClassyLlama\AvaTax\Model\Config;
use AvaTax\ATConfigFactory;

abstract class InteractionAbstract extends DataObject
{
    const API_URL_DEV = 'https://development.avalara.net';
    const API_URL_PROD = 'https://avatax.avalara.net';

    const API_PROFILE_NAME_DEV = 'Development';
    const API_PROFILE_NAME_PROD = 'Production';

    const API_APP_NAME_PREFIX = 'Magento 2';

    /**
     * @var ATConfigFactory
     */
    protected $avaTaxConfigFactory = null;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @param ATConfigFactory $avaTaxConfigFactory
     * @param Config $config
     */
    public function __construct(
        ATConfigFactory $avaTaxConfigFactory,
        Config $config
    ) {
        $this->avaTaxConfigFactory = $avaTaxConfigFactory;
        $this->config = $config;
        $this->createAvaTaxProfile();
    }

    /**
     * Create a development profile and a production profile
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     */
    protected function createAvaTaxProfile()
    {
        $this->avaTaxConfigFactory->create(
            [
                'name' => self::API_PROFILE_NAME_DEV,
                'values' => [
                    'url'       => self::API_URL_DEV,
                    'account'   => $this->config->getDevelopmentAccountNumber(),
                    'license'   => $this->config->getDevelopmentLicenseKey(),
                    'trace'     => true,
                    'client' => $this->generateClientName(),
                    'name' => '',
                ],
            ]
        );

        $this->avaTaxConfigFactory->create(
            [
                'name' => self::API_PROFILE_NAME_PROD,
                'values' => [
                    'url'       => self::API_URL_PROD,
                    'account'   => $this->config->getAccountNumber(),
                    'license'   => $this->config->getLicenseKey(),
                    'trace'     => false,
                    'client' => $this->generateClientName(),
                    'name' => '',
                ],
            ]
        );
    }

    /**
     * Generate AvaTax Client Name from a combination of Magento version number and AvaTax module version number
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @return string
     */
    protected function generateClientName()
    {
        return self::API_APP_NAME_PREFIX . ' - ' . MageAppInterface::VERSION . ' - ' . AvaTaxAppInterface::APP_VERSION;
    }
}