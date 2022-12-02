<?php

namespace ClassyLlama\AvaTax\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class MoveAllowedAttributesConfigToNewConfigPath
 * @package ClassyLlama\AvaTax\Setup\Patch\Data
 */

/**
 * @codeCoverageIgnore
 */
class MoveAllowedAttributesConfigToNewConfigPath implements DataPatchInterface, PatchVersionInterface
{
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
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('core_config_data');
        $pathField = $connection->quoteIdentifier('path');
        $oldConfigPath = $connection->quote("tax/avatax_advanced/attribute_codes/");
        $newConfigPath = $connection->quote("tax/avatax_advanced_attribute_codes/");
        $connection->update(
            $table,
            [
                'path' => new \Zend_Db_Expr(
                    'REPLACE(' . $pathField . ',' . $oldConfigPath . ', ' . $newConfigPath . ')'
                )
            ],
            [$pathField . ' LIKE ?' => 'tax/avatax_advanced/attribute_codes/%']
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritDoc
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
        return '2.2.4.2';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
