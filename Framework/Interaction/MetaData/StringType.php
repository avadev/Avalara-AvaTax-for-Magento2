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

class StringType extends MetaDataAbstract
{
    /**
     * @param string $name
     * @param array $data
     */
    public function __construct($name, array $data = [])
    {
        parent::__construct('string', $name, $data);
    }

    /**
     * Set length of metadata object
     *
     * @param int $length
     * @return boolean
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
     * Get format of metadata object
     * Valid for string type
     * Returns true if format is valid for this type and false if not
     *
     * @param string $format
     * @return bool
     * @throws LocalizedException
     */
    public function setFormat($format)
    {
        if (!is_string($format) || preg_match($format, '') === false) {
            throw new LocalizedException(__('Format must be a valid regular expression.  You passed "%1"', [$format]));
        }
        $this->data[self::ATTR_FORMAT] = $format;
        return false;
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
        $value = $this->validateSimpleType($value);
        $value = $this->validateOptions($value);
        $value = $this->validateLength($value);
        $value = $this->validateFormat($value);

        return $value;
    }

    /**
     * Validate length and trim if necessary
     *
     * @param $value
     * @return string
     */
    protected function validateLength($value)
    {
        if ($this->getLength() > 0 &&
            strlen($value) > $this->getLength()) {
            $value = substr($value, 0, $this->getLength());
        }

        return $value;
    }

    /**
     * Validate format
     *
     * @param $value
     * @return mixed
     * @throws ValidationException
     */
    protected function validateFormat($value)
    {
        $format = $this->getFormat();
        if (!empty($format) &&
            !preg_match($this->getFormat(), $value)) {
            throw new \ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException(
                __('AvaTax requires %1 field to match the regex: "%2"', [
                    $this->getName(),
                    $this->getFormat(),
                ]
            ));
        }

        return $value;
    }
}
