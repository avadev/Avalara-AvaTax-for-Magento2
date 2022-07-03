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
namespace ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Queue extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('baseprovider_queue_job', 'job_id');
    }
}
