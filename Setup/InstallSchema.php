<?php

namespace ClassyLlama\AvaTax\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
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
        $table = $installer->getConnection()
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
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'avatax_queue'
         */
        $table = $installer->getConnection()
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
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
