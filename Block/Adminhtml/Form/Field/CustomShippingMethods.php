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

/**
 * @codeCoverageIgnore
 */
class CustomShippingMethods extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var string
     */
    const SHIPPING_MODE_ID = 'shipping_mode_id';

    /**
     * @var string
     */
    const CUSTOM_SHIPPING_CODE_ID = 'custom_shipping_code_id';

    /**
     * @var ShippingModes
     */
    protected $shippingMethodBlock;

    /**
     * @param string $config
     *
     * @return array
     */
    public static function parseSerializedValue($config)
    {
        $parsedValue = (array)json_decode($config ?? '', true);
        $shippingCodesById = [];

        foreach ($parsedValue as $value) {
            $shippingCodesById[$value[self::CUSTOM_SHIPPING_CODE_ID]] = $value[self::SHIPPING_MODE_ID];
        }

        return $shippingCodesById;
    }

    /**
     * Get activation options.
     * @return ShippingModes
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getShippingMethodRenderer()
    {
        if (!$this->shippingMethodBlock) {
            $this->shippingMethodBlock = $this->getLayout()->createBlock(
                ShippingModes::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->shippingMethodBlock;
    }

    /**
     * Prepare to render.
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            self::SHIPPING_MODE_ID,
            [
                'label' => __('Shipping Mode'),
                'renderer' => $this->_getShippingMethodRenderer()
            ]
        );
        $this->addColumn(self::CUSTOM_SHIPPING_CODE_ID, ['label' => __('Shipping Method Code')]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        $customAttribute = $row->getData(self::SHIPPING_MODE_ID);

        $key = 'option_' . $this->_getShippingMethodRenderer()->calcOptionHash($customAttribute);
        $options[$key] = 'selected="selected"';
        $row->setData('option_extra_attrs', $options);
    }
}
