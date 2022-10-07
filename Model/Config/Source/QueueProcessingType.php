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

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class QueueProcessingType
 *
 * @package ClassyLlama\AvaTax\Model\Config\Source
 */
class QueueProcessingType implements OptionSourceInterface
{
    const NORMAL = 'normal';
    const BATCH = 'batch';
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NORMAL, 'label' => __('Normal')],
            ['value' => self::BATCH, 'label' => __('Batch')],
        ];
    }
}
