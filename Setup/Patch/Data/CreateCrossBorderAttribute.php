<?php

namespace ClassyLlama\AvaTax\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class CreateCrossBorderAttribute
 * @package ClassyLlama\AvaTax\Setup\Patch\Data
 */

/**
 * @codeCoverageIgnore
 */
class CreateCrossBorderAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /**
         * \Magento\Eav\Setup\EavSetup $eavSetup
         */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, strtolower(\ClassyLlama\AvaTax\Helper\CustomsConfig::PRODUCT_ATTR_CROSS_BORDER_TYPE));

        if (!$attribute->getId()) {
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
