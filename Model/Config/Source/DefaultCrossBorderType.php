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

namespace ClassyLlama\AvaTax\Model\Config\Source;

class DefaultCrossBorderType implements \Magento\Framework\Option\ArrayInterface
{
    const BORDER_TYPE_GROUND = 'ground';
    const BORDER_TYPE_OCEAN = 'ocean';
    const BORDER_TYPE_AIR = 'air';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::BORDER_TYPE_GROUND, 'label' => __('Ground')],
            ['value' => self::BORDER_TYPE_OCEAN, 'label' => __('Ocean')],
            ['value' => self::BORDER_TYPE_AIR, 'label' => __('Air')]
        ];
    }
}
