<?php

namespace ClassyLlama\AvaTax\Model\Logger;

class AvaTaxHandler extends \Magento\Framework\Logger\Handler\System
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/avatax.log';
}