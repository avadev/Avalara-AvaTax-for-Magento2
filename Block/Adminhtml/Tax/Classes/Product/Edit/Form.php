<?php
namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Product\Edit;

use ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Product\NewClass;

/**
 * Create form
 */
class Form extends NewClass\Form
{
    use \ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Base\Edit\Form;

    /**
     * Tax class type
     *
     * @var null|string
     */
    protected $classType = \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT;
}
