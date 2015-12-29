<?php

namespace ClassyLlama\AvaTax\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param AvaTaxLogger $logger
     */
    public function __construct(
        AvaTaxLogger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        // Logging
        $this->logger->info('ClassyLlama_AvaTax Schema Installation');

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
                ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
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
            ->setComment('AvaTax Queue Table');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
