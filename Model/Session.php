<?php

namespace ClassyLlama\AvaTax\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\Phrase;

class Session extends \Magento\Framework\Session\SessionManager
{
    protected $addressCacheFields = ['Line1', 'Line2', 'Line3', 'City', 'Region', 'PostalCode', 'Country'];

    protected $addressResponseFields = ['AddressCode', 'Line1', 'Line2', 'Line3', 'City', 'Region', 'PostalCode', 'Country', 'TaxRegionId', 'Line4', 'County', 'FipsCode', 'CarrierRoute', 'PostNet', 'AddressType', 'Latitude', 'Longitude'];

    /**
     * @var null|array
     */
    protected $addressResponses = null;

    /**
     * @var null|array
     */
    protected $taxResponses = null;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Session constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param ObjectManager $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        ObjectManager $objectManager
    ) {
        $this->objectManager = $objectManager;
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );
    }

    /**
     * Add address request response pair to session and cache by both
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \AvaTax\Address $requestAddress
     * @param \AvaTax\ValidAddress $responseAddress
     * @return $this
     * @throws LocalizedException
     */
    public function addAddressResponse(\AvaTax\Address $requestAddress, \AvaTax\ValidAddress $responseAddress)
    {
        $validAddress = $this->getObjectAsArray($this->addressResponseFields, $responseAddress);

        $cacheKey = $this->getCacheKey($this->addressCacheFields, $requestAddress);
        $this->addressResponses[$cacheKey] = $validAddress;

        $cacheKey = $this->getCacheKey($this->addressCacheFields, $responseAddress);
        $this->addressResponses[$cacheKey] = $validAddress;

        $this->storage->setData('address_responses', $this->addressResponses);

        return $this;
    }

    /**
     * Load address data from session if it exists and return as \AvaTax\ValidAddress object or null if not found
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \AvaTax\Address $address
     * @return \AvaTax\ValidAddress|null
     * @throws LocalizedException
     */
    public function getAddressResponse(\AvaTax\Address $address)
    {
        $validAddress = null;
        if (empty($this->addressResponses)) {
            $this->addressResponses = $this->storage->getData('address_responses', $this->addressResponses);
            if (!is_array($this->addressResponses)) {
                $this->addressResponses = [];
                return null;
            }
        }

        $cacheKey = $this->getCacheKey($this->addressCacheFields, $address);

        if (isset($this->addressResponses[$cacheKey])) {
            $validAddress = $this->getArrayAsObject(
                $this->addressResponseFields,
                $this->addressResponses[$cacheKey],
                '\AvaTax\ValidAddress'
            );
        }

        return $validAddress;
    }

    /**
     * Create cache key by calling specified methods and concatenating and hashing
     * TODO: Get Anya to update to create a new tag off of master so that getCountry on \AvaTax\Address works correctly
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $methods
     * @param $object
     * @return string
     * @throws LocalizedException
     */
    protected function getCacheKey($methods, $object)
    {
        $cacheKey = '';
        foreach ($methods as $field) {
            $methodName = 'get' . $field;
            if (method_exists($object, $methodName)) {
                $cacheKey .= call_user_func([$object, $methodName]);
            } else {
                throw new LocalizedException(
                    new Phrase('The method for the passed in field "%1" could not be found.', [$field])
                );
            }
        }

        return hash('md5', $cacheKey);
    }

    protected function getObjectAsArray($methods, $object)
    {
        $objectAsArray = [];
        foreach ($methods as $field) {
            $methodName = 'get' . $field;
            if (method_exists($object, $methodName)) {
                $objectAsArray[$field] = call_user_func([$object, $methodName]);
            } else {
                throw new LocalizedException(
                    new Phrase('The method for the passed in field "%1" could not be found.', [$field])
                );
            }
        }

        return $objectAsArray;
    }

    protected function getArrayAsObject($methods, $array, $objectType)
    {
        $object = $this->objectManager->create($objectType);
        foreach ($methods as $field) {
            $methodName = 'set' . $field;
            if (method_exists($object, $methodName)) {
                call_user_func([$object, $methodName], $array[$field]);
            } else {
                throw new LocalizedException(
                    new Phrase('The method for the passed in field "%1" could not be found.', [$field])
                );
            }
        }

        return $object;
    }

    /**
     * Reset core session hosts after reseting session ID
     *
     * @return $this
     */
    public function regenerateId()
    {
        parent::regenerateId();
        $this->_cleanHosts();
        return $this;
    }
}
