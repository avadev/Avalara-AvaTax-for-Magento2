<?php

namespace ClassyLlama\AvaTax\Block\Adminhtml\Order\Create\Totals;

use Magento\Sales\Block\Adminhtml\Order\Create\Totals\DefaultTotals;

/**
 * Class Tax
 *
 * @package ClassyLlama\AvaTax\Block\Adminhtml\Order\Create\Totals
 */
/**
 * @codeCoverageIgnore
 */
class Tax extends DefaultTotals
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'ClassyLlama_AvaTax::order/create/totals/tax.phtml';
}
