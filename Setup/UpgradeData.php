<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Setup;

use ClassyLlama\AvaTax\Helper\CustomsConfig;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Define connection name to connect to 'sales' database on split database install; falls back to default for a
     * conventional install
     *
     * @var string
     */
    private static $connectionName = 'sales';

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetupFactory;
    }

    /**
     * Upgrade scripts
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * \Magento\Eav\Setup\EavSetup $eavSetup
         */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.0', '<')) {

            // Only copy data and drop columns from "sales_invoice" if the columns exist
            $tableName = $setup->getTable('sales_invoice');
            if (
                $setup->getConnection(self::$connectionName)
                    ->tableColumnExists($tableName, 'avatax_is_unbalanced')
                && $setup->getConnection(self::$connectionName)
                    ->tableColumnExists($tableName, 'base_avatax_tax_amount')
            ) {
                // Copy any existing AvaTax data from core Invoice table into new AvaTax Invoice table
                $select = $setup->getConnection(self::$connectionName)->select()
                    ->from(
                        $setup->getTable('sales_invoice'),
                        [
                            'entity_id',
                            'avatax_is_unbalanced',
                            'base_avatax_tax_amount'
                        ])
                    ->where('base_avatax_tax_amount IS NOT NULL OR avatax_is_unbalanced IS NOT NULL');
                $select = $setup->getConnection(self::$connectionName)->insertFromSelect(
                    $select,
                    $setup->getTable('avatax_sales_invoice'),
                    [
                        'parent_id',
                        'is_unbalanced',
                        'base_avatax_tax_amount'
                    ]
                );
                $setup->getConnection(self::$connectionName)->query($select);

                // Drop "avatax_is_unbalanced" column from "sales_invoice" table
                $setup->getConnection(self::$connectionName)
                    ->dropColumn(
                        $setup->getTable('sales_invoice'),
                        'avatax_is_unbalanced'
                    );

                // Drop "base_avatax_tax_amount" column from "sales_invoice" table
                $setup->getConnection(self::$connectionName)
                    ->dropColumn(
                        $setup->getTable('sales_invoice'),
                        'base_avatax_tax_amount'
                    );
            }

            // Only copy data and drop columns from "sales_creditmemo" if the columns exist
            $tableName = $setup->getTable('sales_creditmemo');
            if (
                $setup->getConnection(self::$connectionName)
                    ->tableColumnExists($tableName, 'avatax_is_unbalanced')
                && $setup->getConnection(self::$connectionName)
                    ->tableColumnExists($tableName, 'base_avatax_tax_amount')
            ) {
                // Copy any existing AvaTax data from core Credit Memo table into new AvaTax Credit Memo table
                $select = $setup->getConnection(self::$connectionName)->select()
                    ->from(
                        $setup->getTable('sales_creditmemo'),
                        [
                            'entity_id',
                            'avatax_is_unbalanced',
                            'base_avatax_tax_amount'
                        ])
                    ->where('base_avatax_tax_amount IS NOT NULL OR avatax_is_unbalanced IS NOT NULL');
                $select = $setup->getConnection(self::$connectionName)->insertFromSelect(
                    $select,
                    $setup->getTable('avatax_sales_creditmemo'),
                    [
                        'parent_id',
                        'is_unbalanced',
                        'base_avatax_tax_amount'
                    ]
                );
                $setup->getConnection(self::$connectionName)->query($select);

                // Drop "avatax_is_unbalanced" column from "sales_creditmemo" table
                $setup->getConnection(self::$connectionName)
                    ->dropColumn(
                        $setup->getTable('sales_creditmemo'),
                        'avatax_is_unbalanced'
                    );

                // Drop "base_avatax_tax_amount" column from "sales_creditmemo" table
                $setup->getConnection(self::$connectionName)
                    ->dropColumn(
                        $setup->getTable('sales_creditmemo'),
                        'base_avatax_tax_amount'
                    );
            }
        }

        /**
         * For conversion to REST, initially disable module and delete pre-existing credentials
         */
        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $connection = $setup->getConnection();

            /** @var \Magento\Framework\DB\Select $select */
            $select = $connection->select()
                ->from(['main' => 'core_config_data'])
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
        }

        /**
         * Create cross border type product attribute
         */
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                strtolower(\ClassyLlama\AvaTax\Helper\CustomsConfig::PRODUCT_ATTR_CROSS_BORDER_TYPE),
                [
                    'group'      => 'AvaTax',
                    'type'       => 'int',
                    'label'      => 'AvaTax Cross Border Type',
                    'input'      => 'text',
                    // TODO: Update input type and add source model once Cross Border Type entity is available
                    'sort_order' => 10,
                    'required'   => false,
                    'global'     => ScopedAttributeInterface::SCOPE_STORE,
                ]
            );
        }

        /**
         * Create Importer of Record Override Option
         */
        if (version_compare($context->getVersion(), '2.0.5', '<')) {

            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();

            /** @var AttributeSet $attributeSet */
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
            $customerSetup->addAttribute(
                Customer::ENTITY,
                CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_ATTRIBUTE,
                [
                    'type'         => 'text',
                    'label'        => 'Override Avatax "Is Seller Importer of Record" setting',
                    'input'        => 'select',
                    'note'         => 'Overrides Importer of Record. Select "Use Default" to keep the Avatax setting, "Override '
                        .
                        'to Yes" to set Customer as Importer of record, "Override to No" to set the Customer as ' .
                        'not the Importer of Record.',
                    'visible'      => true,
                    'user_defined' => 0,
                    'required'     => false,
                    'sort_order'   => 999,
                    'position'     => 999,
                    'system'       => 0,
                    'backend'      => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'source'       => \ClassyLlama\AvaTax\Model\Config\Source\CrossBorderClass\Customer\ImporterOfRecord::class
                ]
            );
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY,
                CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_ATTRIBUTE
            )->addData([
                'attribute_set_id'   => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms'      => ['adminhtml_customer'],
            ]);

            $attribute->save();
        }

        $setup->endSetup();
    }
}
