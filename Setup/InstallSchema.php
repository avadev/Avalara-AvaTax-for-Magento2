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

namespace ClassyLlama\AvaTax\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Define connection name to connect to 'sales' database on split database install; falls back to default for a
     * conventional install
     * @var string
     */
    private static $connectionName = 'sales';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'avatax_log'
         */
        $table = $installer->getConnection(self::$connectionName)
            ->newTable(
                $installer->getTable('avatax_log')
            )
            ->addColumn(
                'log_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'identity' => true,
                    'primary' => true
                ],
                'Log ID'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Log Time'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Store ID'
            )
            ->addColumn(
                'level',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                [],
                'Log Level'
            )
            ->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Message Being Logged'
            )
            ->addColumn(
                'source',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Code Source Reference'
            )
            ->addColumn(
                'request',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Request'
            )
            ->addColumn(
                'result',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Result'
            )
            ->addColumn(
                'additional',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Additional'
            )
            ->addIndex(
                $installer->getIdxName(
                    'avatax_log',
                    ['created_at'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['created_at'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $installer->getIdxName(
                    'avatax_log',
                    [
                        'level',
                        'created_at'
                    ],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                [
                    'level',
                    'created_at'
                ],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->setComment('AvaTax Log Table');
        $installer->getConnection(self::$connectionName)->createTable($table);

        /**
         * Create table 'avatax_queue'
         */
        $table = $installer->getConnection(self::$connectionName)
            ->newTable(
                $installer->getTable('avatax_queue')
            )
            ->addColumn(
                'queue_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'identity' => true,
                    'primary' => true
                ],
                'Queue ID'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Queue Time'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Updated Time'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Store ID'
            )
            ->addColumn(
                'entity_type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Entity Type ID'
            )
            ->addColumn(
                'entity_type_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [],
                'Entity Type Code'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Entity ID'
            )
            ->addColumn(
                'increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [],
                'Increment ID'
            )
            ->addColumn(
                'queue_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [],
                'Queue Status'
            )
            ->addColumn(
                'attempts',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Processing Attempts'
            )
            ->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Message'
            )
            ->addIndex(
                $installer->getIdxName(
                    'avatax_queue',
                    [
                        'queue_status',
                        'created_at',
                        'updated_at'
                    ],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                [
                    'queue_status',
                    'created_at',
                    'updated_at'
                ],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $installer->getIdxName(
                    'avatax_log',
                    ['updated_at'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['updated_at'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $installer->getIdxName(
                    'avatax_queue',
                    [
                        'entity_type_id',
                        'entity_id'
                    ],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [
                    'entity_type_id',
                    'entity_id'
                ],
                [
                    'type' => AdapterInterface::INDEX_TYPE_UNIQUE
                ]
            )
            ->setComment('AvaTax Queue Table');
        $installer->getConnection(self::$connectionName)->createTable($table);

        /**
         * Create table 'avatax_sales_invoice'
         */
        $table = $setup->getConnection(self::$connectionName)
            ->newTable(
                $setup->getTable('avatax_sales_invoice')
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'identity' => true,
                    'primary' => true
                ],
                'Entity ID'
            )
            ->addColumn(
                'parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Sales Invoice ID'
            )
            ->addColumn(
                'is_unbalanced',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                [
                    'nullable' => true,
                    'default' => null,
                    'unsigned' => true,
                ],
                'Is Unbalanced In Relation To AvaTax Calculated Tax Amount'
            )
            ->addColumn(
                'base_avatax_tax_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => true,
                    'default' => null,
                    'unsigned' => false,
                ],
                'Base AvaTax Calculated Tax Amount'
            )
            ->addIndex(
                $setup->getIdxName(
                    'avatax_sales_invoice',
                    [
                        'entity_id',
                        'parent_id'
                    ],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                [
                    'entity_id',
                    'parent_id'
                ],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addForeignKey(
                $setup->getFkName(
                    'avatax_sales_invoice_parent_id_sales_invoice_entity_id',
                    'parent_id',
                    'sales_invoice',
                    'entity_id'
                ),
                'parent_id',
                $setup->getTable('sales_invoice'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('AvaTax Sales Invoice Table');
        $setup->getConnection(self::$connectionName)->createTable($table);

        /**
         * Create table 'avatax_sales_creditmemo'
         */
        $table = $setup->getConnection(self::$connectionName)
            ->newTable(
                $setup->getTable('avatax_sales_creditmemo')
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'identity' => true,
                    'primary' => true
                ],
                'Entity ID'
            )
            ->addColumn(
                'parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Sales Credit Memo ID'
            )
            ->addColumn(
                'is_unbalanced',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                [
                    'nullable' => true,
                    'default' => null,
                    'unsigned' => true,
                ],
                'Is Unbalanced In Relation To AvaTax Calculated Tax Amount'
            )
            ->addColumn(
                'base_avatax_tax_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => true,
                    'default' => null,
                    'unsigned' => false,
                ],
                'Base AvaTax Calculated Tax Amount'
            )
            ->addIndex(
                $setup->getIdxName(
                    'avatax_sales_creditmemo',
                    [
                        'entity_id',
                        'parent_id'
                    ],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                [
                    'entity_id',
                    'parent_id'
                ],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addForeignKey(
                $setup->getFkName(
                    'avatax_sales_creditmemo_parent_id_sales_creditmemo_entity_id',
                    'parent_id',
                    'sales_creditmemo',
                    'entity_id'
                ),
                'parent_id',
                $setup->getTable('sales_creditmemo'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('AvaTax Sales Credit Memo Table');
        $setup->getConnection(self::$connectionName)->createTable($table);

        $installer->endSetup();
    }
}
