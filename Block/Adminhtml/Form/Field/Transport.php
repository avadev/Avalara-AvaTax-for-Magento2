<?php

namespace ClassyLlama\AvaTax\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use ClassyLlama\AvaTax\Block\Adminhtml\Form\Field\TransportShippingColumn;
use ClassyLlama\AvaTax\Block\Adminhtml\Form\Field\TransportColumn;

/**
 * Class Transport
 */
/**
 * @codeCoverageIgnore
 */
class Transport extends AbstractFieldArray
{
    /**
     * @var TransportShippingColumn
     */
    private $transportShippingRenderer;
    /**
     * @var TransportColumn
     */
    private $transportRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('transport_shipping', [
            'label' => __('Shipping Method'),
            'renderer' => $this->getTransportShippingRenderer()
        ]);
        $this->addColumn('transport', [
            'label' => __('Transport'),
            'renderer' => $this->getTransportRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $transport = $row->getTransport();
        if ($transport !== null) {
            $options['option_' . $this->getTransportShippingRenderer()->calcOptionHash($transport)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return TransportShippingColumn
     * @throws LocalizedException
     */
    private function getTransportShippingRenderer()
    {
        if (!$this->transportShippingRenderer) {
            $this->transportShippingRenderer = $this->getLayout()->createBlock(
                TransportShippingColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->transportShippingRenderer;
    }
    /**
     * @return TransportColumn
     * @throws LocalizedException
     */
    private function getTransportRenderer()
    {
        if (!$this->transportRenderer) {
            $this->transportRenderer = $this->getLayout()->createBlock(
                TransportColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->transportRenderer;
    }
}