<?php

namespace ClassyLlama\AvaTax\Api\Data;

interface ImporterOfRecordInterface
{
    /**
     * @return mixed
     */
    public function getExtensionAttribute();

    /**
     * @return mixed
     */
    public function setExtensionAttribute();
}