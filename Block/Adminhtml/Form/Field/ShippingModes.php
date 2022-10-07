<?php

namespace ClassyLlama\AvaTax\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;

/**
 * @codeCoverageIgnore
 */
class ShippingModes extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \ClassyLlama\AvaTax\Model\Config\Source\DefaultCrossBorderType
     */
    protected $defaultCrossBorderType;

    /**
     * @param \ClassyLlama\AvaTax\Model\Config\Source\DefaultCrossBorderType $defaultCrossBorderType
     * @param Context                                                        $context
     * @param array                                                          $data
     */
    public function __construct(
        \ClassyLlama\AvaTax\Model\Config\Source\DefaultCrossBorderType $defaultCrossBorderType,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->defaultCrossBorderType = $defaultCrossBorderType;
    }

    /**
     * @param string $value
     *
     * @return ShippingModes
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Parse to html.
     *
     * @return mixed
     */
    public function _toHtml()
    {
        foreach ($this->defaultCrossBorderType->toOptionArray() as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        return parent::_toHtml();
    }
}
