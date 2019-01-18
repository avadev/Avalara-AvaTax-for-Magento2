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

namespace ClassyLlama\AvaTax\Plugin\Sales\Admin\Order\Create\Form;


class Account
{
    /**
     * @var \ClassyLlama\AvaTax\Helper\Config
     */
    protected $avataxConfigHelper;

    public function __construct(
        \ClassyLlama\AvaTax\Helper\Config $avataxConfigHelper
    ) {
        $this->avaTaxConfigHelper = $avataxConfigHelper;
    }

    public function afterToHtml(\Magento\Sales\Block\Adminhtml\Order\Create\Form\Account $subject, $result)
    {
        if ($this->avataxConfigHelper->isModuleEnabled()) {
            $html = '<div>';
            $html .= __('If you are changing a customer group that affects tax calculation, please see <a href="https://github.com/classyllama/ClassyLlama_AvaTax/blob/develop/docs/getting-started.md#admin-order-create-sales-tax-issue">this readme</a> for how to ensure the appropriate taxes are calculated.');
            $html .= '</div>';

            $result .= $html;
        }

        return $result;
    }
}