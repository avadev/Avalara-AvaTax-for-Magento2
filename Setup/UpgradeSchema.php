<?php

namespace ClassyLlama\AvaTax\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
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
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.1.3', '<')) {

            $this->logger->info(__('ClassyLlama_AvaTax Schema Upgrade to 0.1.3'));

            // Add column to invoice and credit memo tables for avatax responses
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_invoice'),
                'avatax_is_unbalanced',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 1,
                    'nullable' => true,
                    'default' => null,
                    'unsigned' => true,
                    'comment' => 'Is Unbalanced In Relation To AvaTax Calculated Tax Amount'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_creditmemo'),
                'avatax_is_unbalanced',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 1,
                    'nullable' => true,
                    'default' => null,
                    'unsigned' => true,
                    'comment' => 'Is Unbalanced In Relation To AvaTax Calculated Tax Amount'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_invoice'),
                'base_avatax_tax_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'default' => null,
                    'unsigned' => false,
                    'comment' => 'Base AvaTax Calculated Tax Amount'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_creditmemo'),
                'base_avatax_tax_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'default' => null,
                    'unsigned' => false,
                    'comment' => 'Base AvaTax Calculated Tax Amount'
                ]
            );
        }


        if (version_compare($context->getVersion(), '0.1.3', '<')) {

            // Logging
            $this->logger->info('ClassyLlama_AvaTax Schema Upgrade to 0.1.3');

            // TODO: Add unique constraint to the entity_id column of the avatax_creditmemo and avatax_invoice tables

            /**
             * Create table 'avatax_creditmemo'
             */
            $table = $setup->getConnection()
                ->newTable(
                    $setup->getTable('avatax_invoice')
                )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'identity' => true,
                        'primary' => true
                    ],
                    'AvaTax Credit Memo ID'
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
                    'is_unbalanced',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true],
                    'Is Unbalanced In Relation To AvaTax Calculated Tax Amount Response'
                )
                ->addColumn(
                    'base_avatax_tax_amount',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Base AvaTax Calculated Tax Amount'
                );
            $setup->getConnection()->createTable($table);

            $table = $setup->getConnection()
                ->newTable(
                    $setup->getTable('avatax_creditmemo')
                )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'identity' => true,
                        'primary' => true
                    ],
                    'AvaTax Credit Memo ID'
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
                    'is_unbalanced',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true],
                    'Is Unbalanced In Relation To AvaTax Calculated Tax Amount Response'
                )
                ->addColumn(
                    'base_avatax_tax_amount',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Base AvaTax Calculated Tax Amount'
                );
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            // Logging
            $this->logger->info('ClassyLlama_AvaTax Schema Upgrade to 0.1.4');

            /**
             * Add "avatax_code" column to tax_class table
             */
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('tax_class'),
                    'avatax_code',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'AvaTax Code'
                    ]
                );
        }

        $setup->endSetup();
    }
}
