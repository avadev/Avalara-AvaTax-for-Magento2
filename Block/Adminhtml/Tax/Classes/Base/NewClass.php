<?php

namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Base;

/**
 * Class NewClass
 */
abstract class NewClass extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Tax class type
     *
     * @var null|string
     */
    protected $classType = null;

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_tax_classes_' .  \strtolower($this->classType);
        $this->_blockGroup = 'ClassyLlama_AvaTax';
        $this->_mode = 'newClass';

        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Tax Class'));
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('New Tax Class');
    }
}
