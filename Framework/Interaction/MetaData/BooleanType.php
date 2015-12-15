<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\MetaData;

use Magento\Framework\Phrase;

class BooleanType extends MetaDataAbstract
{
    /**
     * @param string $name
     * @param array $data
     */
    public function __construct($name, array $data = [])
    {
        parent::__construct('boolean', $name, $data);
    }

    /**
     * Set valid options of metadata object
     * Valid for integer, string, float
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
     * Pass in a value and get the validated value back
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param mixed $value
     * @return mixed
     * @throws ValidationException
     */
    public function validateData($value)
    {
        $value = $this->validateSimpleType($value);

        return $value;
    }
}