<?php

namespace ClassyLlama\AvaTax\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class Config
{
    // Connection Details
    const XML_PATH_AVATAX_CONNECTION_LIVE_MODE = 'tax/avatax_connection/live_mode';

    const XML_PATH_AVATAX_CONNECTION_ACCOUNT_NUMBER = 'tax/avatax_connection/account_number';

    const XML_PATH_AVATAX_CONNECTION_LICENSE_KEY = 'tax/avatax_connection/license_key';

    const XML_PATH_AVATAX_CONNECTION_COMPANY_CODE = 'tax/avatax_connection/company_code';

    const XML_PATH_AVATAX_CONNECTION_DEVELOPMENT_ACCOUNT_NUMBER = 'tax/avatax_connection/development_account_number';

    const XML_PATH_AVATAX_CONNECTION_DEVELOPMENT_LICENSE_KEY = 'tax/avatax_connection/development_license_key';

    const XML_PATH_AVATAX_CONNECTION_DEVELOPMENT_COMPANY_CODE = 'tax/avatax_connection/development_company_code';

    /**
     * Object attributes
     *
     * @var array
     */
    protected $data = [];

    /**
     * Setter/Getter underscore transformation cache
     *
     * @var array
     */
    protected static $underscoreCache = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig = null;

    /**
     * @var array
     */
    protected $configDefinition = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;

        /**
         * $configDefinition structure:
         * [
         *    '_key_' => [
         *       'paths' => ['_try/path/first_', '_try/path/second_', ...],
         *       'type' => '_bool|string|int|float_',
         *    ],
         *    ...
         * ]
         */
        $this->configDefinition = [
            'live_mode' => [
                'paths' => [self::XML_PATH_AVATAX_CONNECTION_LIVE_MODE],
                'type' => 'bool'
            ],
            'account_number' => [
                'paths' => [self::XML_PATH_AVATAX_CONNECTION_ACCOUNT_NUMBER],
                'type' => 'string',
            ],
            'license_key' => [
                'paths' => [self::XML_PATH_AVATAX_CONNECTION_LICENSE_KEY],
                'type' => 'string',
            ],
            'company_code' => [
                'paths' => [self::XML_PATH_AVATAX_CONNECTION_COMPANY_CODE],
                'type' => 'string',
            ],
            'development_account_number' => [
                'paths' => [
                    self::XML_PATH_AVATAX_CONNECTION_DEVELOPMENT_ACCOUNT_NUMBER,
                    self::XML_PATH_AVATAX_CONNECTION_ACCOUNT_NUMBER,
                ],
                'type' => 'string',
            ],
            'development_license_key' => [
                'paths' => [
                    self::XML_PATH_AVATAX_CONNECTION_DEVELOPMENT_LICENSE_KEY,
                    self::XML_PATH_AVATAX_CONNECTION_LICENSE_KEY,
                ],
                'type' => 'string',
            ],
            'development_company_code' => [
                'paths' => [
                    self::XML_PATH_AVATAX_CONNECTION_DEVELOPMENT_COMPANY_CODE,
                    self::XML_PATH_AVATAX_CONNECTION_COMPANY_CODE,
                ],
                'type' => 'string',
            ],
        ];
    }

    /**
     * Get value from data array and storeId if it is there.
     * Load config value and convert to correct type if it is not there.
     *
     * @param   string $key
     * @return  mixed
     * @throws LocalizedException
     */
    protected function _getData($key, Store $store = null)
    {
        $storeId = ($store === null) ? 'default' : $store->getId();
        if (!isset($this->data[$key]) || !in_array($storeId, $this->data[$key])) {
            if (!isset($this->data[$key])) {
                $this->data[$key] = [];
            }
            if (!array_key_exists($key, $this->configDefinition)) {
                throw new LocalizedException(
                    new Phrase('Invalid method %1::%2(%3, %4)',
                        [
                            get_class($this),
                            'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))),
                            $key,
                            $store,
                        ]
                    )
                );
            }
            $paths = $this->configDefinition[$key]['paths'];
            if (!is_array($paths)) {
                $paths = [$paths];
            }
            if (isset($this->configDefinition[$key]['type']) &&
                in_array($this->configDefinition[$key]['type'], ['string', 'float', 'int', 'bool'])) {
                $type = $this->configDefinition[$key]['type'];
            } else {
                $type = null;
            }
            foreach ($paths as $path) {
                $configValue = $this->scopeConfig->getValue(
                    $path,
                    ScopeInterface::SCOPE_STORE,
                    $store
                );

                if (!empty($configValue)) {
                    break;
                }
            }
            if ($type) {
                if (!settype($configValue, $type)) {
                    throw new LocalizedException(new Phrase('Could not convert "%1" to a "%2"', [$configValue, $type]));
                }
            }
            $this->data[$key][$storeId] = $configValue;
        }
        return $this->data[$key][$storeId];
    }

    /**
     * Object data getter
     *
     * If $key is not defined will return all the data as an array.
     * Otherwise it will return value of the element specified by $key.
     *
     * If $store is specified it will pass it along, otherwise it will use default store
     *
     * @param string     $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key = '', Store $store = null)
    {
        if ('' === $key) {
            return $this->data;
        }

        return $this->_getData($key, $store);
    }

    /**
     * Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     * @throws LocalizedException
     */
    public function __call($method, $args)
    {
        if (substr($method, 0, 3) == 'get') {
            $key = $this->underscore(substr($method, 3));
            $store = isset($args[0]) ? $args[0] : null;
            return $this->getData($key, $store);
        }
        throw new LocalizedException(
            new Phrase('Invalid method %1::%2(%3)', [get_class($this), $method, print_r($args, 1)])
        );
    }

    /**
     * Converts field names for setters and getters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unnecessary preg_replace
     *
     * @param string $name
     * @return string
     */
    protected function underscore($name)
    {
        if (isset(self::$underscoreCache[$name])) {
            return self::$underscoreCache[$name];
        }
        $result = strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $name), '_'));
        self::$underscoreCache[$name] = $result;
        return $result;
    }

// TODO: Maybe remove this sample
//    /**
//     * Default override example
//     *
//     * @author Jonathan Hodges <jonathan@classyllama.com>
//     * @param Store $store
//     * @return bool
//     */
//    public function getConnectionMode(Store $store = null)
//    {
//        $key = 'connection_mode';
//        $storeId = ($store === null) ? 'default' : $store->getId();
//        if (!in_array($storeId, $this->data[$key])) {
//            $this->data[$key][$storeId] = (bool)$this->_scopeConfig->getValue(
//                self::XML_PATH_AVATAX_CONNECTION_LIVE_MODE,
//                ScopeInterface::SCOPE_STORE,
//                $store
//            );
//        }
//        return $this->data[$key][$storeId];
//    }
}