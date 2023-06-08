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
namespace ClassyLlama\AvaTax\BaseProvider\Model\Config\Source\Application;

class LoggingMode implements \Magento\Framework\Data\OptionSourceInterface
{
    const LOGGING_MODE_DB = 1;
    const LOGGING_MODE_FILE = 2;

    public function toOptionArray()
    {
        return [
            [
                'value' => self::LOGGING_MODE_DB,
                'label' => __('Database')
            ],
            [
                'value' => self::LOGGING_MODE_FILE,
                'label' => __('File')
            ]
        ];
    }

    public function toArray()
    {
        return [self::LOGGING_MODE_DB => __('Database'),self::LOGGING_MODE_FILE => __('File')];
    }
}
