<?php

namespace ClassyLlama\AvaTax\Setup\Patch\Data;

use ClassyLlama\AvaTax\Helper\CustomsConfig;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class CreateImporterRecordOverrideOption
 * @package ClassyLlama\AvaTax\Setup\Patch\Data
 */

/**
 * @codeCoverageIgnore
 */
class CreateImporterRecordOverrideOption implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY,
            CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_ATTRIBUTE);

        if (!$attribute->getId()) {
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
                    'source'       => \ClassyLlama\AvaTax\Model\Config\Source\CrossBorderClass\Customer\ImporterOfRecord::class,
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId
                ]
            );

            $data = [];
            $newAttribute = $customerSetup->getAttribute(Customer::ENTITY, CustomsConfig::CUSTOMER_IMPORTER_OF_RECORD_ATTRIBUTE);
            if (isset($newAttribute['attribute_id']) && $newAttribute['attribute_id']) {
                $data[] = ['form_code' => 'adminhtml_customer', 'attribute_id' => $newAttribute['attribute_id']];
                if ($data) {
                    $this->moduleDataSetup->getConnection()
                        ->insertMultiple($this->moduleDataSetup->getTable('customer_form_attribute'), $data);
                }
            }
        }

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
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
