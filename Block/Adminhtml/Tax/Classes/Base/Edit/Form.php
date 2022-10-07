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

namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Base\Edit;

use ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Customer\NewClass;

/**
 * Using a trait since the users of this trait will need to extend from their respective tax class types
 */
/**
 * @codeCoverageIgnore
 */
trait Form
{
    /**
     * Modify structure of new status form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $form->getElement('base_fieldset')->removeField('is_new');
        $form->setAction(
            $this->getUrl('avatax/tax_classes_' .  \strtolower($this->classType) . '/save', ['id' => $this->getRequest()->getParam('id')])
        );
        return $this;
    }
}
