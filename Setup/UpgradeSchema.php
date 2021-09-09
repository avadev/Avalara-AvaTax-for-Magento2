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

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Define connection name to connect to 'sales' database on split database install; falls back to default for a
     * conventional install
     *
     * @var string
     */
    private static $connectionName = 'sales';

    /**
     * Define connection name to connect to 'quote' database on split database install; falls back to default for a
     * conventional install
     *
     * @var string
     */
    private static $checkoutConnectionName = 'checkout';

    /**
     * {@inheritdoc}
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            /**
             * Add "avatax_code" column to tax_class table
             */
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('tax_class'),
                    'avatax_code',
                    [
                        'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length'   => 255,
                        'nullable' => true,
                        'default'  => null,
                        'comment'  => 'AvaTax Code'
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
                        'primary'  => true
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
                        'default'  => null,
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
                        'default'  => null,
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
                        'primary'  => true
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
                        'default'  => null,
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
                        'default'  => null,
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

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            /**
             * Create table 'avatax_cross_border_class'
             */
            $table = $setup->getConnection()
                ->newTable(
                    $setup->getTable('avatax_cross_border_class')
                )
                ->addColumn(
                    'class_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'identity' => true,
                        'primary'  => true
                    ],
                    'Class ID'
                )
                ->addColumn(
                    'cross_border_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Cross Border Type'
                )
                ->addColumn(
                    'hs_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                        'default'  => '',
                    ],
                    'HS Code'
                )
                ->addColumn(
                    'unit_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Unit Name'
                )
                ->addColumn(
                    'unit_amount_product_attr',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Unit Amount Product Attribute'
                )
                ->addColumn(
                    'pref_program_indicator',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                    ],
                    'Pref. Program Indicator'
                )
                ->setComment('Cross Border Class');

            $setup->getConnection()->createTable($table);


            /**
             * Create table 'avatax_cross_border_class_country'
             */
            $table = $setup->getConnection()
                ->newTable(
                    $setup->getTable('avatax_cross_border_class_country')
                )
                ->addColumn(
                    'link_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'identity' => true,
                        'primary'  => true
                    ],
                    'Link ID'
                )
                ->addColumn(
                    'class_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Class ID'
                )
                ->addColumn(
                    'country_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    2,
                    [
                        'nullable' => false,
                    ],
                    'Country ID'
                )
                ->addIndex(
                    $setup->getIdxName(
                        'avatax_cross_border_class_country',
                        ['class_id', 'country_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['class_id', 'country_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addForeignKey(
                    $setup->getFkName(
                        'avatax_cross_border_class_country',
                        'class_id',
                        'avatax_cross_border_class',
                        'class_id'
                    ),
                    'class_id',
                    $setup->getTable('avatax_cross_border_class'),
                    'class_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $setup->getFkName(
                        'avatax_cross_border_class_country',
                        'country_id',
                        'directory_country',
                        'country_id'
                    ),
                    'country_id',
                    $setup->getTable('directory_country'),
                    'country_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('AvaTax Sales Credit Memo Table');

            $setup->getConnection()->createTable($table);
        }

        // TODO: Consolidate with initial table creation above
        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('avatax_cross_border_class'),
                'cross_border_type',
                'cross_border_type_id',
                [
                    'type'     => 'integer',
                    'unsigned' => true,
                    'nullable' => true,
                    'length'   => 11,
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $installer = $setup;
            $installer->startSetup();

            $table_classyllama_avatax_crossbordertype = $setup->getConnection()
                ->newTable($setup->getTable('classyllama_avatax_crossbordertype'));


            $table_classyllama_avatax_crossbordertype->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
                'Entity ID'
            );


            $table_classyllama_avatax_crossbordertype->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'type'
            );


            $setup->getConnection()->createTable($table_classyllama_avatax_crossbordertype);

            $setup->endSetup();
        }

        // TODO: Add foreign key on avatax_cross_border_class.cross_border_type

        $extensionTables = [
            'avatax_quote_item'            => [
                'id_field'       => 'quote_item_id',
                'id_field_label' => 'Quote Item ID',
                'foreign_table'  => 'quote_item',
                'foreign_field'  => 'item_id',
                'comment'        => 'AvaTax Quote Item Extension',
                'connection'     => self::$checkoutConnectionName,
            ],
            'avatax_sales_order_item'      => [
                'id_field'       => 'order_item_id',
                'id_field_label' => 'Order Item ID',
                'foreign_table'  => 'sales_order_item',
                'foreign_field'  => 'item_id',
                'comment'        => 'AvaTax Order Item Extension',
                'connection'     => self::$connectionName,
            ],
            'avatax_sales_invoice_item'    => [
                'id_field'       => 'invoice_item_id',
                'id_field_label' => 'Invoice Item ID',
                'foreign_table'  => 'sales_invoice_item',
                'foreign_field'  => 'entity_id',
                'comment'        => 'AvaTax Invoice Item Extension',
                'connection'     => self::$connectionName,
            ],
            'avatax_sales_creditmemo_item' => [
                'id_field'       => 'creditmemo_item_id',
                'id_field_label' => 'Credit Memo Item ID',
                'foreign_table'  => 'sales_creditmemo_item',
                'foreign_field'  => 'entity_id',
                'comment'        => 'AvaTax Credit Memo Item Extension',
                'connection'     => self::$connectionName,
            ],
        ];

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            foreach ($extensionTables as $tableName => $tableInfo) {
                $table = $setup->getConnection($tableInfo['connection'])
                    ->newTable(
                        $setup->getTable($tableName)
                    )
                    ->addColumn(
                        'id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'nullable' => false,
                            'identity' => true,
                            'primary'  => true
                        ],
                        'ID'
                    )
                    ->addColumn(
                        $tableInfo['id_field'],
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        10,
                        [
                            'nullable' => false,
                            'unsigned' => true,
                        ],
                        $tableInfo['id_field_label']
                    )
                    ->addColumn(
                        'hs_code',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'HS Code'
                    )
                    ->addColumn(
                        'unit_name',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'Unit Name'
                    )
                    ->addColumn(
                        'unit_amount',
                        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        '12,4',
                        [
                            'nullable' => true,
                        ],
                        'Unit Amount'
                    )
                    ->addColumn(
                        'pref_program_indicator',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        [
                            'nullable' => true,
                        ],
                        'Pref. Program Indicator'
                    )
                    ->setComment($tableInfo['comment']);

                if (isset($tableInfo['foreign_table']) && !empty($tableInfo['foreign_table'])) {
                    $table->addForeignKey(
                        $setup->getFkName(
                            $tableName,
                            $tableInfo['id_field'],
                            $tableInfo['foreign_table'],
                            $tableInfo['foreign_field']
                        ),
                        $tableInfo['id_field'],
                        $setup->getTable($tableInfo['foreign_table']),
                        $tableInfo['foreign_field'],
                        Table::ACTION_CASCADE
                    );
                }

                $setup->getConnection($tableInfo['connection'])->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            foreach ($extensionTables as $tableName => $tableInfo) {
                $setup->getConnection($tableInfo['connection'])
                    ->addIndex(
                        $setup->getTable($tableName),
                        $setup->getIdxName(
                            $tableName,
                            [$tableInfo['id_field']],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        [$tableInfo['id_field']],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    );
            }
        }

        if (version_compare($context->getVersion(), '2.0.7', '<')) {
            $setup->getConnection(self::$connectionName)
                ->addColumn(
                    $setup->getTable('avatax_sales_invoice'),
                    'avatax_response',
                    [
                        'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length'   => null,
                        'nullable' => true,
                        'default'  => null,
                        'comment'  => 'AvaTax Response'
                    ]
                );

            $setup->getConnection(self::$connectionName)
                ->addColumn(
                    $setup->getTable('avatax_sales_creditmemo'),
                    'avatax_response',
                    [
                        'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length'   => null,
                        'nullable' => true,
                        'default'  => null,
                        'comment'  => 'AvaTax Response'
                    ]
                );

            $table = $setup->getConnection(self::$connectionName)
                ->newTable(
                    $setup->getTable('avatax_sales_order')
                )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'identity' => true,
                        'primary'  => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Order id'
                )
                ->addColumn(
                    'avatax_response',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => true,
                    ],
                    'HS Code'
                )
                ->addForeignKey(
                    $setup->getFkName(
                        'avatax_sales_order',
                        'order_id',
                        'sales_order',
                        'entity_id'
                    ),
                    'order_id',
                    $setup->getTable('sales_order'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('AvaTax Sales Order');

            $setup->getConnection(self::$connectionName)->createTable($table);
        }

        if (version_compare($context->getVersion(), '2.0.8', '<')) {
            $setup->getConnection(self::$checkoutConnectionName)
                ->addColumn(
                    $setup->getTable('quote_address'),
                    'avatax_messages',
                    [
                        'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length'   => null,
                        'nullable' => true,
                        'default'  => null,
                        'comment'  => 'AvaTax Messages'
                    ]
                );
        }

        if (version_compare($context->getVersion(), '2.1.6', '<')) {
            $this->createQueueBatchTransactionsTable($setup);
        }

        if (version_compare($context->getVersion(), '2.2.4.1', '<')) {
            $setup->getConnection(self::$connectionName)
                ->addIndex(
                    $setup->getTable('avatax_sales_order'),
                    $setup->getIdxName(
                        $setup->getTable('avatax_sales_order'),
                        ['order_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['order_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                );
        }

        $setup->endSetup();
    }

    private function createQueueBatchTransactionsTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable(
                $setup->getTable('avatax_batch_queue')
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'identity' => true,
                    'primary'  => true
                ],
                'Entity Id'
            )
            ->addColumn(
                'batch_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Batch Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                ],
                'Batch Name'
            )
            ->addColumn(
                'company_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Company Id'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                ],
                'Status'
            )
            ->addColumn(
                'record_count',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Status'
            )
            ->addColumn(
                'input_file_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                20,
                [
                    'nullable' => true,
                ],
                'Input File Id'
            )
            ->addColumn(
                'result_file_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                20,
                [
                    'nullable' => true,
                ],
                'Result File Id'
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
            );

        $setup->getConnection()->createTable($table);
    }
}
