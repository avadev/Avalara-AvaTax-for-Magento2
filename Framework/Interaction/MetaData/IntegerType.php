<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\MetaData;

use Magento\Framework\Phrase;

class IntegerType extends MetaDataAbstract
{
    /**
     * @param string $name
     * @param array $data
     */
    public function __construct($name, array $data = [])
    {
        parent::__construct('integer', $name, $data);
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

        return $value;
    }
}
