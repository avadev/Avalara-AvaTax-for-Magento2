<?php

namespace ClassyLlama\AvaTax\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class PrepareInitialConfig
 * @package ClassyLlama\AvaTax\Setup\Patch\Data
 */

/**
 * @codeCoverageIgnore
 */
class PrepareInitialConfig implements DataPatchInterface, PatchVersionInterface
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
        /** @var \Magento\Framework\DB\Select $select */
        $select = $connection->select()
            ->from(['main' => $this->moduleDataSetup->getTable('core_config_data')])
            ->where('main.path IN (?)', [
                'tax/avatax/enabled',
                'tax/avatax/production_account_number',
                'tax/avatax/production_license_key',
                'tax/avatax/production_company_code',
                'tax/avatax/development_account_number',
                'tax/avatax/development_license_key',
                'tax/avatax/development_company_code',
            ]);
        $sql = $select->deleteFromSelect('main');
        $connection->query($sql);

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
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
