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

            $this->logger->info('ClassyLlama_AvaTax Schema Upgrade to 0.1.3');

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

        $setup->endSetup();
    }
}
