<?php
namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Customer\Edit;

use ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Customer\NewClass;

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
    protected $classType = \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER;
}
