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

class DataObjectType extends MetaDataAbstract
{
    /**
     * @param string $name
     * @param array $data
     */
    public function __construct($name, array $data = [])
    {
        parent::__construct('dataObject', $name, $data);
    }

    /**
     * Set valid options of metadata object
     * Valid for integer, string, double (float)
     * Returns true if valid options is valid for this type and false if not
     *
     * @param array $validOptions
     * @return boolean
     */
    public function setOptions(array $validOptions)
    {
        return false;
    }

    /**
     * Set children metadata objects of this metadata object
     * Valid only on array and object types
     * Returns true if children are valid for this type and false if not
     *
     * @param MetaDataObject $subtype
     * @return bool
     */
    public function setSubtype(MetaDataObject $subtype = null)
    {
        $this->data[self::ATTR_SUBTYPE] = $subtype;
        return true;
    }

    /**
     * Set class of metadata object
     * Valid only on object type
     * Returns true if class is valid for this type and false if not
     *
     * @param string $class
     * @return bool
     * @throws LocalizedException
     */
    public function setClass($class)
    {
        if (!is_string($class) || !class_exists($class)) {
            throw new LocalizedException(__('%1 is not a valid class', [$class]));
        }

        $this->data[self::ATTR_CLASS] = $class;
        return true;
    }

    /**
     * Pass in a value and get the validated value back
     *
     * @param mixed $value
     * @return mixed
     * @throws ValidationException
     */
    public function validateData($value)
    {
        if (is_null($value)) {
            return $value;
        }

        if ('object' != getType($value)) {
            if ($this->getRequired()) {
                throw new \ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException(
                    __('The value you passed in is not an object.')
                );
            }
            $value = null;
        }

        $class = $this->getClass();
        if (!is_null($value) && !($value instanceof $class)) {
            throw new \ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException(__(
                'The object you passed in is of type %1 and is required to be of type %2.',
                [
                    get_class($value),
                    $class
                ]
            ));
        }

        return $value;
    }

    /**
     * Returns the cacheable portion of the string version of this object
     *
     * @param \Magento\Framework\DataObject $value
     * @return mixed
     * @internal param $data
     */
    public function getCacheKey($value)
    {
        $cacheKey = '';
        if (!$this->getUseInCacheKey()) {
            return $cacheKey;
        }
        // If a subtype is defined, call this function for that contents of the array
        if (!is_null($this->getSubtype())) {
            $cacheKey = $this->getSubtype()->getCacheKeyFromObject($value);
        } elseif (!is_null($value)) {
            foreach ($value->getData() as $item) {
                if (is_array($item)) {
                    $cacheKey .= $this->getCacheKey($item);
                } else {
                    $cacheKey .= (string) $item;
                }
            }
        }
        return $cacheKey;
    }
}
