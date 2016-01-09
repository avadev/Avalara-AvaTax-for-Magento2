<?php
namespace ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes\Customer;

use ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes\Base;

/**
 * Class Save
 */
class Save extends Base\Save
{
    /**
     * Tax class type
     *
     * @var null|string
     */
    protected $classType = \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER;
}
