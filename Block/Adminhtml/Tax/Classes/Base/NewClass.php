<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Base;

/**
 * Class NewClass
 */
/**
 * @codeCoverageIgnore
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
