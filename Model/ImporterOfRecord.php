<?php

namespace ClassyLlama\AvaTax\Model;

class ImporterOfRecord implements \ClassyLlama\AvaTax\Api\Data\ImporterOfRecordInterface
{
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }
}