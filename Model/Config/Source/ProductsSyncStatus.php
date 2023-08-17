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
class ProductsSyncStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::AVATAX_PRODUCTS_SYNC_PENDING_CODE, 
                'label' => __(Config::AVATAX_PRODUCTS_SYNC_PENDING_TEXT)
            ],
            [
                'value' => Config::AVATAX_PRODUCTS_SYNC_COMPLETED_CODE, 
                'label' => __(Config::AVATAX_PRODUCTS_SYNC_COMPLETED_TEXT)
            ],
            [
                'value' => Config::AVATAX_PRODUCTS_SYNC_IN_PROGRESS_CODE, 
                'label' => __(Config::AVATAX_PRODUCTS_SYNC_IN_PROGRESS_TEXT)
            ]
        ];
    }
}
