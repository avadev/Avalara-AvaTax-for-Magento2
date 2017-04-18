<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Upgrade scripts
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.4.0', '<' )) {

            // Only copy data and drop columns from "sales_invoice" if the columns exist
            if (
                $setup->getConnection()->tableColumnExists('sales_invoice', 'avatax_is_unbalanced')
                && $setup->getConnection()->tableColumnExists('sales_invoice', 'base_avatax_tax_amount')
            ) {
                // Copy any existing AvaTax data from core Invoice table into new AvaTax Invoice table
                $select = $setup->getConnection()->select()
                    ->from(
                        $setup->getTable('sales_invoice'),
                        [
                            'entity_id',
                            'avatax_is_unbalanced',
                            'base_avatax_tax_amount'
                        ])
                    ->where('base_avatax_tax_amount IS NOT NULL OR avatax_is_unbalanced IS NOT NULL');
                $select = $setup->getConnection()->insertFromSelect(
                    $select,
                    $setup->getTable('avatax_sales_invoice'),
                    [
                        'parent_id',
                        'is_unbalanced',
                        'base_avatax_tax_amount'
                    ]
                );
                $setup->getConnection()->query($select);

                // Drop "avatax_is_unbalanced" column from "sales_invoice" table
                $setup->getConnection()
                    ->dropColumn(
                        $setup->getTable('sales_invoice'),
                        'avatax_is_unbalanced'
                    );

                // Drop "base_avatax_tax_amount" column from "sales_invoice" table
                $setup->getConnection()
                    ->dropColumn(
                        $setup->getTable('sales_invoice'),
                        'base_avatax_tax_amount'
                    );
            }

            // Only copy data and drop columns from "sales_creditmemo" if the columns exist
            if (
                $setup->getConnection()->tableColumnExists('sales_creditmemo', 'avatax_is_unbalanced')
                && $setup->getConnection()->tableColumnExists('sales_creditmemo', 'base_avatax_tax_amount')
            ) {
                // Copy any existing AvaTax data from core Credit Memo table into new AvaTax Credit Memo table
                $select = $setup->getConnection()->select()
                    ->from(
                        $setup->getTable('sales_creditmemo'),
                        [
                            'entity_id',
                            'avatax_is_unbalanced',
                            'base_avatax_tax_amount'
                        ])
                    ->where('base_avatax_tax_amount IS NOT NULL OR avatax_is_unbalanced IS NOT NULL');
                $select = $setup->getConnection()->insertFromSelect(
                    $select,
                    $setup->getTable('avatax_sales_creditmemo'),
                    [
                        'parent_id',
                        'is_unbalanced',
                        'base_avatax_tax_amount'
                    ]
                );
                $setup->getConnection()->query($select);

                // Drop "avatax_is_unbalanced" column from "sales_creditmemo" table
                $setup->getConnection()
                    ->dropColumn(
                        $setup->getTable('sales_creditmemo'),
                        'avatax_is_unbalanced'
                    );

                // Drop "base_avatax_tax_amount" column from "sales_creditmemo" table
                $setup->getConnection()
                    ->dropColumn(
                        $setup->getTable('sales_creditmemo'),
                        'base_avatax_tax_amount'
                    );
            }
        }
        $setup->endSetup();
    }
}