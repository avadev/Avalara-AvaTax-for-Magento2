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

namespace ClassyLlama\AvaTax\Block\Checkout;

use Magento\Checkout\Block\Total\DefaultTotal;

/**
 * Class Tax
 *
 * @package ClassyLlama\AvaTax\Block\Checkout
 * @codeCoverageIgnore
 */
class Tax extends DefaultTotal
{
    /**
     * @var string
     */
    protected $_template = 'ClassyLlama_AvaTax::checkout/tax.phtml';
}
