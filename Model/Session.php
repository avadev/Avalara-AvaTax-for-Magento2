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
    /**
     * A unique string for decreasing the likelihood that an object
     * stored as array may have a property which tries to be converted into an object
     */
    const UNIQUE_STRING = 'avatax_unique_string_l3o8dslij';
    
    /**
     * Properties on object to use as cache key
     *
     * @var array
     */
    protected $addressCacheFields = ['Line1', 'Line2', 'Line3', 'City', 'Region', 'PostalCode', 'Country'];

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
     * Add tax request response pair to session and cache by both
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     */
    public function addGetTaxResponse(\AvaTax\GetTaxRequest $requestTax, \AvaTax\GetTaxResult $responseTax)
    {
        $propertiesArray = [];
        $reflectResponseTax = new \ReflectionObject($responseTax);
        $properties = $reflectResponseTax->getProperties();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $propertiesArray[$property->getName()] = $property->getValue($requestTax);

        }
        $validTax = $this->getObjectAsArray($this->taxResponseFields, $responseTax);

        $cacheKey = $this->getCacheKey($this->taxCacheFields, $requestTax);
        $this->taxResponses[$cacheKey] = $validTax;

        $cacheKey = $this->getCacheKey($this->taxCacheFields, $responseTax);
        $this->taxResponses[$cacheKey] = $validTax;

        $this->storage->setData('tax_responses', $this->taxResponses);

        return $this;
    }

    protected function reflectObjectAsArray($object)
    {
        $propertiesArray = [];
        $reflection = new \ReflectionObject($object);
        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $propertiesArray[$property->getName()] = $property->getValue($object);
            if (is_object($propertiesArray[$property->getName()])) {
                $propertiesArray[$property->getName()] = $this->reflectObjectAsArray($propertiesArray[$property->getName()]);
            }
        }

        return ['className' => get_class($object), 'properties' => $propertiesArray, 'is_a_reflection_array' => self::UNIQUE_STRING];
    }

    protected function reflectObjectFromArray(array $reflectionArray)
    {
        if (!isset($reflectionArray['className']) ||
            !isset($reflectionArray['properties']) ||
            !isset($reflectionArray['is_a_reflection_array']) ||
            $reflectionArray['is_a_reflection_array'] != self::UNIQUE_STRING
        ) {
            return false;
        }

        $object = new $reflectionArray['className'];

        $reflection = new \ReflectionClass($reflectionArray['className']);

        $reflectionProperties = $reflection->getProperties();

        $reflectionPropertyMap = [];

        foreach ($reflectionProperties as $reflectionProperty) {
            $reflectionPropertyMap[$reflectionProperty->getName()] = $reflectionProperty;
        }

        foreach ($reflectionArray['properties'] as $propertyName => $property) {
            if (is_array($property) &&
                isset($property['is_a_reflection_array']) &&
                $property['is_a_reflection_array'] == self::UNIQUE_STRING
            ) {
                $property = $this->reflectObjectFromArray($property);
            }
            if (isset($reflectionPropertyMap[$propertyName])) {
                $reflectionPropertyMap[$propertyName]->setAccessible(true);
                $reflectionPropertyMap[$propertyName]->setValue($object, $property);
            }
        }

        return $object;
    }

    /**
     * Load tax data from session if it exists and return as \AvaTax\ValidTax object or null if not found
     *
     */
    public function getGetTaxResponse(\AvaTax\GetTaxRequest $tax)
    {
        $validTax = null;
        if (empty($this->taxResponses)) {
            $this->taxResponses = $this->storage->getData('tax_responses', $this->taxResponses);
            if (!is_array($this->taxResponses)) {
                $this->taxResponses = [];
                return null;
            }
        }

        $cacheKey = $this->getCacheKey($this->taxCacheFields, $tax);

        if (isset($this->taxResponses[$cacheKey])) {
            $validTax = $this->getArrayAsObject(
                $this->taxResponseFields,
                $this->taxResponses[$cacheKey],
                '\AvaTax\ValidTax'
            );
        }

        return $validTax;
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
        $validAddress = $this->reflectObjectAsArray($responseAddress);

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
            $validAddress = $this->reflectObjectFromArray($this->addressResponses[$cacheKey]);
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
