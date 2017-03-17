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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Define connection name to connect to 'sales' database on split database install; falls back to default for a
     * conventional install
     * @var string
     */
    private static $connectionName = 'sales';

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            /**
             * Add "avatax_code" column to tax_class table
             */
            $setup->getConnection(self::$connectionName)
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

        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            /** As a part of the upgrade process for this revision, data is migrated from the sales_invoice and
             * sales_creditmemo tables into the newly created tables above.  After the data migration, the previously
             * mentioned tables are altered to drop the now-unused columns in each (base_avatax_tax_amount and
             * avatax_is_unbalanced). @see \ClassyLlama\AvaTax\Setup\UpgradeData for this logic.
             */

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
        }
        $setup->endSetup();
    }
}
