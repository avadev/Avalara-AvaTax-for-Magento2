<?php

namespace ClassyLlama\AvaTax\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class CopyDataIntoNewTables
 * @package ClassyLlama\AvaTax\Setup\Patch\Data
 */

/**
 * @codeCoverageIgnore
 */
class CopyDataIntoNewTables implements DataPatchInterface, PatchVersionInterface
{
    /**
     * Define connection name to connect to 'sales' database on split database install; falls back to default for a
     * conventional install
     *
     * @var string
     */
    private static $connectionName = 'sales';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        // Only copy data and drop columns from "sales_invoice" if the columns exist
        $tableName = $this->moduleDataSetup->getTable('sales_invoice');
        if (
            $this->moduleDataSetup->getConnection(self::$connectionName)
                ->tableColumnExists($tableName, 'avatax_is_unbalanced')
            && $this->moduleDataSetup->getConnection(self::$connectionName)
                ->tableColumnExists($tableName, 'base_avatax_tax_amount')
        ) {
            // Copy any existing AvaTax data from core Invoice table into new AvaTax Invoice table
            $select = $this->moduleDataSetup->getConnection(self::$connectionName)->select()
                ->from(
                    $this->moduleDataSetup->getTable('sales_invoice'),
                    [
                        'entity_id',
                        'avatax_is_unbalanced',
                        'base_avatax_tax_amount'
                    ])
                ->where('base_avatax_tax_amount IS NOT NULL OR avatax_is_unbalanced IS NOT NULL');
            $select = $this->moduleDataSetup->getConnection(self::$connectionName)->insertFromSelect(
                $select,
                $this->moduleDataSetup->getTable('avatax_sales_invoice'),
                [
                    'parent_id',
                    'is_unbalanced',
                    'base_avatax_tax_amount'
                ]
            );
            $this->moduleDataSetup->getConnection(self::$connectionName)->query($select);
        }

        // Only copy data and drop columns from "sales_creditmemo" if the columns exist
        $tableName = $this->moduleDataSetup->getTable('sales_creditmemo');
        if (
            $this->moduleDataSetup->getConnection(self::$connectionName)
                ->tableColumnExists($tableName, 'avatax_is_unbalanced')
            && $this->moduleDataSetup->getConnection(self::$connectionName)
                ->tableColumnExists($tableName, 'base_avatax_tax_amount')
        ) {
            // Copy any existing AvaTax data from core Credit Memo table into new AvaTax Credit Memo table
            $select = $this->moduleDataSetup->getConnection(self::$connectionName)->select()
                ->from(
                    $this->moduleDataSetup->getTable('sales_creditmemo'),
                    [
                        'entity_id',
                        'avatax_is_unbalanced',
                        'base_avatax_tax_amount'
                    ])
                ->where('base_avatax_tax_amount IS NOT NULL OR avatax_is_unbalanced IS NOT NULL');
            $select = $this->moduleDataSetup->getConnection(self::$connectionName)->insertFromSelect(
                $select,
                $this->moduleDataSetup->getTable('avatax_sales_creditmemo'),
                [
                    'parent_id',
                    'is_unbalanced',
                    'base_avatax_tax_amount'
                ]
            );
            $this->moduleDataSetup->getConnection(self::$connectionName)->query($select);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
