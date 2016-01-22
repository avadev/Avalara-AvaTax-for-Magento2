<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.1.3', '<')) {
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

        if (version_compare($context->getVersion(), '0.1.4', '<')) {
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
