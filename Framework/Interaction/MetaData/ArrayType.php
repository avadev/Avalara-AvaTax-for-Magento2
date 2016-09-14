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

class ArrayType extends MetaDataAbstract
{
    /**
     * @param string $name
     * @param array $data
     */
    public function __construct($name, array $data = [])
    {
        parent::__construct('array', $name, $data);
    }

    /**
     * Set length of metadata object
     * Valid for string and array types
     * Returns true if length is valid for this object type and false if not
     *
     * @param int $length
     * @return bool
     * @throws LocalizedException
     */
    public function setLength($length)
    {
        if (!is_integer($length) || $length <= 0) {
            throw new LocalizedException(__(
                'Length can only be set to integer greater than or equal to 0.  You tried to set it to: %1.',
                [
                    $length
                ]
            ));
        }
        $this->data[self::ATTR_LENGTH] = $length;
        return true;
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
     * Pass in a value and get the validated value back
     * If your data can be converted to an array, please do so explicitly before passing in
     * because automated array conversion will not be attempted since it can have unexpected results.
     *
     * @param mixed $value
     * @return mixed
     * @throws LocalizedException
     */
    public function validateData($value)
    {
        if ($this->getType() != getType($value)) {
            if ($this->getRequired()) {
                throw new \ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException(__(
                    'The value you passed in is not an array. ' .
                    'If your data can be converted to an array, please do so explicitly before passing it in ' .
                    'because automated array conversion will not be attempted since it can have unexpected results.'
                ));
            }
            $value = [];
        }

        // If a subtype is defined, call this function for that contents of the array
        if (!is_null($this->getSubtype())) {
            $value = $this->getSubtype()->validateData($value);
        }

        // If the length exceeds the maximum allowed length, throw an exception
        if ($this->getLength() > 0 && count($value) > $this->getLength()) {
            throw new \ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException(__(
                'You attempted to pass data to the AvaTax API with the key of %1,' . '
                         with a length of %2, the max allowed length is %3.',
                [
                    $this->getName(),
                    count($value),
                    $this->getLength(),
                ]
            ));
        }

        return $value;
    }

    /**
     * Returns the the string version of this array
     *
     * @param $value
     * @return mixed
     */
    public function getCacheKey($value)
    {
        $cacheKey = '';
        if (!$this->getUseInCacheKey()) {
            return $cacheKey;
        }
        // If a subtype is defined, call this function for that contents of the array
        if (!is_null($this->getSubtype())) {
            $cacheKey = $this->getSubtype()->getCacheKey($value);
        } else {
            foreach ($value as $item) {
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
