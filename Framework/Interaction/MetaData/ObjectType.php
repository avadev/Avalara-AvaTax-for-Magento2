<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\MetaData;

use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class ObjectType extends MetaDataAbstract
{
    /**
     * @param string $name
     * @param array $data
     */
    public function __construct($name, array $data = [])
    {
        parent::__construct('object', $name, $data);
    }

    /**
     * Set valid options of metadata object
     * Valid for integer, string, double (float)
     * Returns true if valid options is valid for this type and false if not
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param array $validOptions
     * @return boolean
     */
    public function setValidOptions(array $validOptions)
    {
        return false;
    }

    /**
     * Set class of metadata object
     * Valid only on object type
     * Returns true if class is valid for this type and false if not
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param string $class
     * @return bool
     * @throws LocalizedException
     */
    public function setClass($class)
    {
        if (!is_string($class) || !class_exists($class)) {
            throw new LocalizedException(new Phrase('%1 is not a valid class', [$class]));
        }

        $this->data[self::ATTR_CLASS] = $class;
        return true;
    }

    /**
     * Pass in a value and get the validated value back
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param mixed $value
     * @return mixed
     * @throws ValidationException
     */
    public function validateData($value)
    {
        if ($this->getType() != getType($value)) {
            if ($this->getRequired()) {
                throw new ValidationException(new Phrase('The value you passed in is not an object.'));
            }
            $value = null;
        }

        $class = $this->getClass();
        if (!is_null($value) && !($value instanceof $class)) {
            if ($this->getRequired()) {
                throw new ValidationException(new Phrase(
                    'The object you passed in is of type %1 and is required to be of type %2.',
                    [
                        get_class($value),
                        $class
                    ]
                ));
            }
        }

        return $value;
    }
}