<?php

namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Base;

/**
 * Class Edit
 */
abstract class Edit extends NewClass
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
        parent::_construct();
        $this->_mode = 'edit';
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Edit Tax Class');
    }
}
