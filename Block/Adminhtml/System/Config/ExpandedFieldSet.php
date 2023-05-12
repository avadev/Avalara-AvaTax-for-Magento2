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
namespace ClassyLlama\AvaTax\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * @api
 */
/**
 * @codeCoverageIgnore
 */
class ExpandedFieldSet extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    const PARENT_AVATAX_CONFIGURATION_ID = 'tax_avatax_configuration';

    /**
     * @return string
     */
    public function getConfigSearchParams()
    {
        $expanded = self::PARENT_AVATAX_CONFIGURATION_ID;
        if ($this->getRequest()->getParam('expanded')) {
            $expanded = $this->getRequest()->getParam('expanded');
        }
        return $expanded;
    }

    /**
     * Collapsed or expanded fieldset when page loaded?
     *
     * @param AbstractElement $element
     * @return bool
     */
    protected function _isCollapseState($element)
    {
        $expanded = $this->getConfigSearchParams();
        /** To Open Requested Group */
        if ($element->getId() == $expanded) {
            return true;
        }
        /** To Open Parent of Requested Group */
        if ($element->getId() == self::PARENT_AVATAX_CONFIGURATION_ID && $expanded != self::PARENT_AVATAX_CONFIGURATION_ID) {
            return true;
        }
        return parent::_isCollapseState($element);
    }
}
