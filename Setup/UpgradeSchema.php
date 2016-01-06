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
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Entity ID'
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
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Entity ID'
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

        $setup->endSetup();
    }
}
