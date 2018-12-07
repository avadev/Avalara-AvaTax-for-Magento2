<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Api\SearchCriteria\JoinProcessor;

use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use \Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;

class Order implements CustomJoinInterface
{

    /**
     * Make custom joins to collection
     *
     * @param DbCollection $collection
     *
     * @return bool
     * @since 100.2.0
     */
    public function apply(DbCollection $collection)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $collection->getConnection();

        $collection->getSelect()->joinLeft(
            ['soi' => $connection->getTableName('sales_order_item')],
            new \Zend_Db_Expr('main_table.order_item_id=soi.item_id'),
            ['sales_order_id' => 'order_id']
        );
        return true;
    }
}
