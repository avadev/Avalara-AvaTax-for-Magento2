<?php

namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Customer;

use ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Base;

/**
 * Class Edit
 */
class Edit extends Base\Edit
{
    /**
     * Tax class type
     *
     * @var null|string
     */
    protected $classType = \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER;
}
