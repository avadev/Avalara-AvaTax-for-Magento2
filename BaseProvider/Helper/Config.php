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
namespace ClassyLlama\AvaTax\BaseProvider\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Avalara Queue Config helper
 */
class Config extends AbstractHelper
{    
    const XML_PATH_AVATAX_QUEUE_BATCH_SIZE = 'tax/baseprovider/queue_batch_size';
    const XML_PATH_AVATAX_QUEUE_LIMIT = 'tax/baseprovider/queue_limit';

    /**
     * Return configured Queue Batch Size
     *
     * @return int
     */
    public function getBatchSize()
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_AVATAX_QUEUE_BATCH_SIZE);
    }

    /**
     * Return configured Queue limit
     *
     * @return int
     */
    public function getQueueLimit()
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_AVATAX_QUEUE_LIMIT);
    }
}
