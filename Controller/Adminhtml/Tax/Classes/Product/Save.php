<?php
namespace ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes\Product;

use ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes\Base;

class Save extends Base\Save
{
    /**
     * Tax class type
     *
     * @var null|string
     */
    protected $classType = \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT;
}
