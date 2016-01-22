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

namespace ClassyLlama\AvaTax\Model\Config\Source;

use ClassyLlama\AvaTax\Helper\Config;

class ErrorAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Config::ERROR_ACTION_DISABLE_CHECKOUT, 'label' => __('Disable checkout & show error message')],
            ['value' => Config::ERROR_ACTION_ALLOW_CHECKOUT_NATIVE_TAX, 'label' => __('Allow checkout & fall back to native Magento tax calculation (no error message)')],
        ];
    }
}
