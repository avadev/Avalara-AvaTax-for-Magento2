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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

/**
 * @codeCoverageIgnore
 */
class CompanyCode extends Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return parent::_getElementHtml($element) . $this->getLayout()->createBlock(
                \Magento\Backend\Block\Template::class
            )->setData($this->getData())->setTemplate('ClassyLlama_AvaTax::form/field/company-code.phtml')->toHtml();
    }
}