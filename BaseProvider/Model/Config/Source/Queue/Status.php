<?php
/*
 * Avalara_BaseProvider
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Queue;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    const STATUS_NEW        = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_COMPLETED  = 3;
    const STATUS_FAILED     = 4;

    public function toArray()
    {
        $options = [
            self::STATUS_NEW => __('New'), 
            self::STATUS_PROCESSING => __('Processing'), 
            self::STATUS_COMPLETED => __('Completed'), 
            self::STATUS_FAILED => __('Failed')
        ];
        return $options;
    }
    
    public function toOptionArray()
    {
        $options = $this->toArray();
        $decoratedOptions = [];
        if (count($options) > 0) {
            foreach ($options as $value=>$label) {
                $decoratedOptions[] = ['value'=>$value, 'label'=>$label];
            }
        }
        return $decoratedOptions;
    }
}
