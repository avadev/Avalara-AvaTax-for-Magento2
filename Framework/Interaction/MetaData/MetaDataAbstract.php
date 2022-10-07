<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Framework\Interaction\MetaData;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

abstract class MetaDataAbstract
{
    /**
     * List of types that will be attempted to convert to
     *
     * @var array
     */
    protected $simpleTypes = ['boolean', 'integer', 'string', 'double'];

    /**
     * List of all available types
     *
     * @var array
     */
    public static $types = ['boolean', 'integer', 'string', 'double', 'dataObject', 'array'];

    /**
     * Store all metadata
     *
     * @var array
     */
    protected $data = [
        self::ATTR_LENGTH => 0,
        self::ATTR_REQUIRED => false,
        self::ATTR_FORMAT => '',
        self::ATTR_VALID_OPTIONS => [],
        self::ATTR_CLASS => '',
        self::ATTR_SUBTYPE => null,
        self::ATTR_USE_IN_CACHE_KEY => true,
    ];

    // All available attribute keys
    const ATTR_NAME = 'name';
    const ATTR_TYPE = 'type';
    const ATTR_FORMAT = 'format';
    const ATTR_REQUIRED = 'required';
    const ATTR_CLASS = 'class';
    const ATTR_LENGTH = 'length';
    const ATTR_VALID_OPTIONS = 'options';
    const ATTR_SUBTYPE = 'subtype';
    const ATTR_USE_IN_CACHE_KEY = 'use_in_cache_key';

    /**
     * @param $type
     * @param $name
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct($type, $name, array $data = [])
    {
        if (is_string($type) && is_string($name) && in_array($type, self::$types)) {
            $this->data[self::ATTR_TYPE] = $type;
            $this->data[self::ATTR_NAME] = $name;
        } else {
            throw new LocalizedException(
                __('Both type and name must be strings.  Type must be one of the following: ' .
                    '\'boolean\', \'integer\', \'string\', \'double\', \'object\', \'array\'.')
            );
        }

        foreach ($data as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }
    }

    /**
     * Get type of metadata object
     *
     * @return string
     */
    public function getType()
    {
        return $this->data[self::ATTR_TYPE];
    }

    /**
     * Get length of metadata object
     *
     * @return int|null
     */
    public function getLength()
    {
        return $this->data[self::ATTR_LENGTH];
    }

    /**
     * Set length of metadata object
     * Valid for string and array types
     * Returns true if length is valid for this object type and false if not
     *
     * @param int $length
     * @return boolean
     */
    public function setLength($length)
    {
        return false;
    }

    /**
     * Get whether this property is required
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->data[self::ATTR_REQUIRED];
    }

    /**
     * Set whether this property is required
     *
     * @param boolean
     * @return boolean
     */
    public function setRequired($required)
    {
        $this->data[self::ATTR_REQUIRED] = (bool)$required;
        return true;
    }

    /**
     * Get format of metadata object
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->data[self::ATTR_FORMAT];
    }

    /**
     * Get format of metadata object
     * Valid for string type
     * Returns true if format is valid for this type and false if not
     *
     * @param string $format
     * @return boolean
     */
    public function setFormat($format)
    {
        return false;
    }

    /**
     * Get valid options of metadata object
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->data[self::ATTR_VALID_OPTIONS];
    }

    /**
     * Set valid options of metadata object
     * Valid for integer, string, double (float)
     * Returns true if valid options is valid for this type and false if not
     *
     * @param array $validOptions
     * @return boolean
     * @throws LocalizedException
     */
    public function setOptions(array $validOptions)
    {
        foreach ($validOptions as $validOption) {
            if (getType($validOption) !== $this->data[self::ATTR_TYPE]) {
                throw new LocalizedException(
                    __(
                        'All valid options must be of type %1, you passed one of type %2.',
                        [
                            $this->data[self::ATTR_TYPE],
                            getType($validOption),
                        ]
                    )
                );
            }
        }
        $this->data[self::ATTR_VALID_OPTIONS] = $validOptions;

        return true;
    }

    /**
     * Get class of metadata object
     *
     * @return string
     */
    public function getClass()
    {
        return $this->data[self::ATTR_CLASS];
    }

    /**
     * Set class of metadata object
     * Valid only on object type
     * Returns true if class is valid for this type and false if not
     *
     * @param string $class
     * @return boolean
     */
    public function setClass($class)
    {
        return false;
    }

    /**
     * Get children metadata objects of this metadata object
     *
     * @return MetaDataObject
     */
    public function getSubtype()
    {
        return $this->data[self::ATTR_SUBTYPE];
    }

    /**
     * Set children metadata objects of this metadata object
     * Valid only on array and object types
     * Returns true if children are valid for this type and false if not
     *
     * @param MetaDataObject $subtype
     * @return bool
     */
    public function setSubtype(MetaDataObject $subtype)
    {
        return false;
    }

    /**
     * Get whether to use in cache key of metadata object
     *
     * @return boolean
     */
    public function getUseInCacheKey()
    {
        return $this->data[self::ATTR_USE_IN_CACHE_KEY];
    }

    /**
     * Set whether to use in cache key of metadata object
     *
     * @param boolean $useInCacheKey
     * @return boolean
     */
    public function setUseInCacheKey($useInCacheKey)
    {
        $this->data[self::ATTR_USE_IN_CACHE_KEY] = (bool)$useInCacheKey;
        return true;
    }

    /**
     * Get name of metadata object
     *
     * @return string
     */
    public function getName()
    {
        return $this->data[self::ATTR_NAME];
    }

    /**
     * Pass in a value and get the validated value back
     *
     * @param mixed $value
     * @return mixed
     * @throws LocalizedException
     */
    public function validateData($value)
    {
        return $value;
    }

    /**
     * Validate whether value is one of required options
     *
     * @param mixed $value
     * @return mixed $value
     * @throws ValidationException
     */
    protected function validateOptions($value)
    {
        // Make sure the value is a valid option if options are set
        if (!empty($this->getOptions()) && !in_array($value, $this->getOptions())) {
            if ($this->getRequired()) {
                throw new \ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException(__(
                    'The value you passed in is not one of the valid options.  Valid Options are: %1',
                    [
                        print_r($this->getOptions(), true)
                    ]
                ));
            }
            $value = '';
        }

        return $value;
    }

    /**
     * Validates type and converts it if can
     *
     * @param $value
     * @return mixed
     * @throws ValidationException
     */
    protected function validateSimpleType($value)
    {
        if (gettype($value) != $this->getType()) {
            // For simple types, try to convert a value to the correct type.
            try {
                settype($value, $this->getType());
            } catch (\Exception $e) {
                throw new \ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException(
                    __('Could not convert "%1" to a "%2"', [
                       $this->getName(),
                      $this->getType(),
                    ]
                ));
            }
        }

        return $value;
    }

    /**
     * Returns the cacheable portion of the string version of this object
     *
     * @param $value
     * @return mixed
     */
    public function getCacheKey($value)
    {
        if ($this->getUseInCacheKey()) {
            return (string)$value;
        } else {
            return '';
        }
    }
}
