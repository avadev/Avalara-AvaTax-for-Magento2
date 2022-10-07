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

namespace ClassyLlama\AvaTax\Tests\Integration\Model\Tax\Sales\Total\Quote;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Calculation;

class SetupUtil
{
    /**
     * Default tax related configurations
     *
     * @var array
     */
    protected $defaultConfig = [
        Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS => '0',
        Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX => 0, //Excluding tax
        Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX => 0, //Excluding tax
        Config::CONFIG_XML_PATH_BASED_ON => 'shipping', // or 'billing'
        Config::CONFIG_XML_PATH_APPLY_ON => '0',
        Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT => '0',
        Config::CONFIG_XML_PATH_DISCOUNT_TAX => '0',
        Config::XML_PATH_ALGORITHM => Calculation::CALC_TOTAL_BASE,
    ];

    const TAX_RATE_MI = 'tax_rate_mi';
    const TAX_RATE_SHIPPING = 'tax_rate_shipping';
    const TAX_STORE_RATE = 'tax_store_rate';
    const COUNTRY_US = 'US';

    /**#@+
     * Values for default address and taxing jurisdiction (6% flat state tax)
     */
    const REGION_MI = '33';
    const MI_POST_CODE = '48933';
    const MI_CITY = 'Lansing';
    const MI_STREET_1 = '100 N Capitol Ave'; // Michigan state capitol
    const AVATAX_MI_RATE_DESCRIPTION = 'MI STATE TAX';
    const AVATAX_MI_RATE_JURISCODE = 26;
    /**#@-*/

    /**#@+
     * Values for optional address and taxing jurisdiction (8% combined tax)
     */
    const REGION_CA = '12';
    const SAN_DIEGO_POST_CODE = '92101';
    const SAN_DIEGO_CITY = 'San Diego';
    const SAN_DIEGO_STREET_1 = '2920 Zoo Dr'; // San Diego Zoo
    const AVATAX_CA_RATE_DESCRIPTION = 'CA STATE TAX';
    const AVATAX_CA_RATE_JURISCODE = '06';
    const AVATAX_CA_COUNTY_RATE_DESCRIPTION = 'CA COUNTY TAX';
    const AVATAX_CA_COUNTY_RATE_JURISCODE = '037';
    const AVATAX_CA_SAN_DIEGO_SPECIAL_RATE_DESCRIPTION = 'CA SPECIAL TAX';
    const AVATAX_CA_SAN_DIEGO_SPECIAL_RATE_JURISCODE = 'EMBD0';
    /**#@-*/

    /**
     * Tax rates
     *
     * @var array
     */
    protected $taxRates = [
        self::TAX_RATE_SHIPPING => [
            'data' => [
                'tax_country_id' => self::COUNTRY_US,
                'tax_region_id' => '*',
                'tax_postcode' => '*',
                'code' => self::TAX_RATE_SHIPPING,
                'rate' => '7.5',
            ],
            'id' => null,
        ],
        self::TAX_STORE_RATE => [
            'data' => [
                'tax_country_id' => self::COUNTRY_US,
                'tax_region_id' => self::REGION_CA,
                'tax_postcode' => '*',
                'code' => self::TAX_STORE_RATE,
                'rate' => '8',
            ],
            'id' => null,
        ],
        self::TAX_RATE_MI => [
            'data' => [
                'tax_country_id' => self::COUNTRY_US,
                'tax_region_id' => self::REGION_MI,
                'tax_postcode' => '*',
                'code' => self::TAX_RATE_MI,
                'rate' => '6',
            ],
            'id' => null,
        ],
    ];

    const PRODUCT_TAX_CLASS_1 = 'product_tax_class_1';
    const PRODUCT_TAX_CLASS_2 = 'product_tax_class_2';
    const PRODUCT_TAX_CLASS_3_DIGITAL_GOODS = 'product_tax_class_3_digital_goods';
    const SHIPPING_TAX_CLASS = 'shipping_tax_class';

    /**
     * List of product tax class that will be created.
     *
     * The ID of the created tax classes will be stored as the values for each of the keys
     *
     * @var array
     */
    protected $productTaxClasses = [
        self::PRODUCT_TAX_CLASS_1 => null,
        self::PRODUCT_TAX_CLASS_2 => null,
        // This tax class is for digital goods
        self::PRODUCT_TAX_CLASS_3_DIGITAL_GOODS => null,
        self::SHIPPING_TAX_CLASS => null,
    ];

    /**
     * Information to use when creating tax classes listed above
     *
     * @var array
     */
    protected $productTaxClassesCreationData = [
        self::PRODUCT_TAX_CLASS_1 => ['avatax_code' => ''],
        self::PRODUCT_TAX_CLASS_2 => ['avatax_code' => ''],
        self::PRODUCT_TAX_CLASS_3_DIGITAL_GOODS => ['avatax_code' => 'D0000000'],
        self::SHIPPING_TAX_CLASS => null,
    ];

    const CUSTOMER_TAX_CLASS_1 = 'customer_tax_class_1';
    const CUSTOMER_TAX_CLASS_2_NON_PROFIT = 'customer_tax_class_2_non_profit';
    const CUSTOMER_PASSWORD = 'password';

    /**
     * List of customer tax class to be created
     *
     * The ID of the created tax classes will be stored as the values for each of the keys
     *
     * @var array
     */
    protected $customerTaxClasses = [
        self::CUSTOMER_TAX_CLASS_1 => null,
        self::CUSTOMER_TAX_CLASS_2_NON_PROFIT => null,
    ];

    /**
     * Information to use when creating tax classes listed above
     *
     * @var array
     */
    protected $customerTaxClassesCreationData = [
        self::CUSTOMER_TAX_CLASS_1 => ['avatax_code' => ''],
        /**
         * "E" is the code for a Charitable Organization.
         * @see \ClassyLlama\AvaTax\Model\Config\Source\AvaTaxCustomerUsageType::toOptionArray
         */
        self::CUSTOMER_TAX_CLASS_2_NON_PROFIT => ['avatax_code' => 'E'],
    ];

    /**
     * List of tax rules
     *
     * @var array
     */
    protected $taxRules = [];

    const CONFIG_OVERRIDES = 'config_overrides';
    const TAX_RATE_OVERRIDES = 'tax_rate_overrides';
    const TAX_RULE_OVERRIDES = 'tax_rule_overrides';

    /**
     * Default data for shopping cart rule
     *
     * @var array
     */
    protected $defaultShoppingCartPriceRule = [
        'name' => 'Shopping Cart Rule',
        'is_active' => 1,
        'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::CUST_GROUP_ALL],
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'simple_action' => 'by_percent',
        'discount_amount' => 40,
        'stop_rules_processing' => 1,
        'website_ids' => [1],
    ];

    /**
     * Name to be used for configurable attribute
     */
    const CONFIGURABLE_ATTRIBUTE_NAME = 'config_attribute';

    /**
     * Storage for customer so that it can be reused by this test
     * @see \ClassyLlama\AvaTax\Tests\Integration\Model\Tax\Sales\Total\Quote\TaxTest::testNativeVsMagentoTaxCalculation
     *
     * @var null
     */
    protected $customer = null;

    /**
     * Storage for configurable attributes so they can be retrieved after being created
     *
     * @var array
     */
    protected $configurableAttributes = [];

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct($objectManager)
    {
        $this->objectManager = $objectManager;
        $this->customerRepository = $this->objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->productRepository = $this->objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->accountManagement = $this->objectManager->create('Magento\Customer\Api\AccountManagementInterface');
    }

    /**
     * Create customer tax classes
     *
     * @return $this
     */
    protected function createCustomerTaxClass()
    {
        foreach (array_keys($this->customerTaxClasses) as $className) {
            $extraData = [];
            if (isset($this->customerTaxClassesCreationData[$className])) {
                $extraData = $this->customerTaxClassesCreationData[$className];
            }
            $this->customerTaxClasses[$className] = $this->objectManager->create('Magento\Tax\Model\ClassModel')
                ->setData($extraData)
                ->setClassName($className)
                ->setClassType(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER)
                ->save()
                ->getId();
        }

        return $this;
    }

    /**
     * Create product tax classes
     *
     * @return $this
     */
    protected function createProductTaxClass()
    {
        foreach (array_keys($this->productTaxClasses) as $className) {
            $extraData = [];
            if (isset($this->productTaxClassesCreationData[$className])) {
                $extraData = $this->productTaxClassesCreationData[$className];
            }
            $this->productTaxClasses[$className] = $this->objectManager->create('Magento\Tax\Model\ClassModel')
                ->setData($extraData)
                ->setClassName($className)
                ->setClassType(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT)
                ->save()
                ->getId();
        }

        return $this;
    }

    /**
     * Set the configuration.
     *
     * @param array $configData
     * @return $this
     */
    public function setConfig($configData)
    {
        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = $this->objectManager->get('Magento\Config\Model\ResourceModel\Config');
        foreach ($configData as $path => $value) {
            if ($path == Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS) {
                $value = $this->productTaxClasses[$value];
            }
            $config->saveConfig(
                $path,
                $value,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = $this->objectManager->get('Magento\Framework\App\Config\ReinitableConfigInterface');
        $config->reinit();

        return $this;
    }

    /**
     * Create tax rates
     *
     * @param array $overrides
     * @return $this
     */
    protected function createTaxRates($overrides)
    {
        $taxRateOverrides = empty($overrides[self::TAX_RATE_OVERRIDES]) ? [] : $overrides[self::TAX_RATE_OVERRIDES];
        foreach (array_keys($this->taxRates) as $taxRateCode) {
            if (isset($taxRateOverrides[$taxRateCode])) {
                $this->taxRates[$taxRateCode]['data']['rate'] = $taxRateOverrides[$taxRateCode];
            }
            $this->taxRates[$taxRateCode]['id'] = $this->objectManager->create('Magento\Tax\Model\Calculation\Rate')
                ->setData($this->taxRates[$taxRateCode]['data'])
                ->save()
                ->getId();
        }
        return $this;
    }

    /**
     * Convert the code to id for productTaxClass, customerTaxClass and taxRate in taxRuleOverrideData
     *
     * @param array $taxRuleOverrideData
     * @param array $taxRateIds
     * @return array
     */
    protected function processTaxRuleOverrides($taxRuleOverrideData, $taxRateIds)
    {
        if (!empty($taxRuleOverrideData['customer_tax_class_ids'])) {
            $customerTaxClassIds = [];
            foreach ($taxRuleOverrideData['customer_tax_class_ids'] as $customerClassCode) {
                $customerTaxClassIds[] = $this->customerTaxClasses[$customerClassCode];
            }
            $taxRuleOverrideData['customer_tax_class_ids'] = $customerTaxClassIds;
        }
        if (!empty($taxRuleOverrideData['product_tax_class_ids'])) {
            $productTaxClassIds = [];
            foreach ($taxRuleOverrideData['product_tax_class_ids'] as $productClassCode) {
                $productTaxClassIds[] = $this->productTaxClasses[$productClassCode];
            }
            $taxRuleOverrideData['product_tax_class_ids'] = $productTaxClassIds;
        }
        if (!empty($taxRuleOverrideData['tax_rate_ids'])) {
            $taxRateIdsForRule = [];
            foreach ($taxRuleOverrideData['tax_rate_ids'] as $taxRateCode) {
                $taxRateIdsForRule[] = $taxRateIds[$taxRateCode];
            }
            $taxRuleOverrideData['tax_rate_ids'] = $taxRateIdsForRule;
        }

        return $taxRuleOverrideData;
    }

    /**
     * Return a list of product tax class ids NOT including shipping product tax class
     *
     * @return array
     */
    protected function getProductTaxClassIds()
    {
        $productTaxClassIds = [];
        foreach ($this->productTaxClasses as $productTaxClassName => $productTaxClassId) {
            if ($productTaxClassName != self::SHIPPING_TAX_CLASS) {
                $productTaxClassIds[] = $productTaxClassId;
            }
        }

        return $productTaxClassIds;
    }

    /**
     * Return a list of tax rate ids NOT including shipping tax rate
     *
     * @return array
     */
    protected function getDefaultTaxRateIds()
    {
        $taxRateIds = [
            $this->taxRates[self::TAX_RATE_MI]['id'],
            $this->taxRates[self::TAX_STORE_RATE]['id'],
        ];

        return $taxRateIds;
    }

    /**
     * Return the default customer group tax class id
     *
     * @return int
     */
    public function getDefaultCustomerTaxClassId()
    {
        /** @var  \Magento\Customer\Api\GroupManagementInterface $groupManagement */
        $groupManagement = $this->objectManager->get('Magento\Customer\Api\GroupManagementInterface');
        $defaultGroup = $groupManagement->getDefaultGroup();
        return $defaultGroup->getTaxClassId();
    }

    /**
     * Create tax rules
     *
     * @param array $overrides
     * @return $this
     */
    protected function createTaxRules($overrides)
    {
        $taxRateIds = [];
        foreach ($this->taxRates as $taxRateCode => $taxRate) {
            $taxRateIds[$taxRateCode] = $taxRate['id'];
        }

        //The default customer tax class id is used to calculate store tax rate
        $customerClassIds = [
            $this->customerTaxClasses[self::CUSTOMER_TAX_CLASS_1],
            $this->getDefaultCustomerTaxClassId()
        ];

        //By default create tax rule that covers all product tax classes except SHIPPING_TAX_CLASS
        //The tax rule will cover all tax rates except TAX_RATE_SHIPPING
        $taxRuleDefaultData = [
            'code' => 'Test Rule',
            'priority' => '0',
            'position' => '0',
            'customer_tax_class_ids' => $customerClassIds,
            'product_tax_class_ids' => $this->getProductTaxClassIds(),
            'tax_rate_ids' => $this->getDefaultTaxRateIds(),
        ];

        //Create tax rules
        if (empty($overrides[self::TAX_RULE_OVERRIDES])) {
            //Create separate shipping tax rule
            $shippingTaxRuleData = [
                'code' => 'Shipping Tax Rule',
                'priority' => '0',
                'position' => '0',
                'customer_tax_class_ids' => $customerClassIds,
                'product_tax_class_ids' => [$this->productTaxClasses[self::SHIPPING_TAX_CLASS]],
                'tax_rate_ids' => [$this->taxRates[self::TAX_RATE_SHIPPING]['id']],
            ];
            $this->taxRules[$shippingTaxRuleData['code']] = $this->objectManager
                ->create('Magento\Tax\Model\Calculation\Rule')
                ->setData($shippingTaxRuleData)
                ->save()
                ->getId();

            //Create a default tax rule
            $this->taxRules[$taxRuleDefaultData['code']] = $this->objectManager
                ->create('Magento\Tax\Model\Calculation\Rule')
                ->setData($taxRuleDefaultData)
                ->save()
                ->getId();
        } else {
            foreach ($overrides[self::TAX_RULE_OVERRIDES] as $taxRuleOverrideData ) {
                //convert code to id for productTaxClass, customerTaxClass and taxRate
                $taxRuleOverrideData = $this->processTaxRuleOverrides($taxRuleOverrideData, $taxRateIds);
                $mergedTaxRuleData = array_merge($taxRuleDefaultData, $taxRuleOverrideData);
                $this->taxRules[$mergedTaxRuleData['code']] = $this->objectManager
                    ->create('Magento\Tax\Model\Calculation\Rule')
                    ->setData($mergedTaxRuleData)
                    ->save()
                    ->getId();
            }
        }

        return $this;
    }

    /**
     * Set up tax classes, tax rates and tax rules
     * The override structure:
     * override['self::CONFIG_OVERRIDES']
     *      [
     *          [config_path => config_value]
     *      ]
     * override['self::TAX_RATE_OVERRIDES']
     *      [
     *          ['tax_rate_code' => tax_rate]
     *      ]
     * override['self::TAX_RULE_OVERRIDES']
     *      [
     *          [
     *              'code' => code //Required, has to be unique
     *              'priority' => 0
     *              'position' => 0
     *              'tax_customer_class' => array of customer tax class names as defined in this class
     *              'tax_product_class' => array of product tax class names as defined in this class
     *              'tax_rate' => array of tax rate codes as defined in this class
     *          ]
     *      ]
     *
     * @param array $overrides
     * @return void
     */
    public function setupTax($overrides)
    {
        //Create product tax classes
        $this->createProductTaxClass();

        //Create customer tax classes
        $this->createCustomerTaxClass();

        //Create tax rates
        $this->createTaxRates($overrides);

        //Create tax rules
        $this->createTaxRules($overrides);

        //Tax calculation configuration
        if (!empty($overrides[self::CONFIG_OVERRIDES])) {
            $this->setConfig($overrides[self::CONFIG_OVERRIDES]);
        }
    }

    /**
     * Create a simple product with given sku, price and tax class
     *
     * @param string $sku
     * @param float $price
     * @param int $taxClassId
     * @param array|null $additionalAttributes
     * @return \Magento\Catalog\Model\Product
     */
    public function createSimpleProduct($sku, $price, $taxClassId, $additionalAttributes = [])
    {
        /** @var \Magento\Catalog\Model\Product $product */
        if ($this->loadProductBySku($sku)) {
            $product = $this->loadProductBySku($sku);
        } else {
            /** @var $product \Magento\Catalog\Model\Product */
            $product = $this->objectManager->create('Magento\Catalog\Model\Product');
            $product->isObjectNew(true);
        }

        $product->setTypeId('simple')
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Simple Product' . $sku)
            ->setSku($sku)
            ->setPrice($price)
            ->setTaxClassId($taxClassId)
            ->setStockData(
                [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_qty_decimal' => 0,
                    'is_in_stock' => 1
                ]
            )->setMetaTitle('meta title')
            ->setMetaKeyword('meta keyword')
            ->setMetaDescription('meta description')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        foreach ($additionalAttributes as $key => $value) {
            $product->setData($key, $value);
        }

        $product->save();

        $product = $product->load($product->getId());
        $this->products[$sku] = $product;
        return $product;
    }

    /**
     * Create configurable product and children
     *
     * This file was inspired by
     * @see dev/tests/integration/testsuite/Magento/ConfigurableProduct/_files/product_configurable.php
     *
     * @param $sku
     * @param $price
     * @param $taxClassId
     * @param $itemData
     * @return \Magento\Catalog\Model\Product
     */
    protected function createConfigurableProduct($sku, $price, $taxClassId, $itemData)
    {
        $options = $itemData['options'];

        $attribute = $this->createConfigurableAttribute(self::CONFIGURABLE_ATTRIBUTE_NAME, $options);

        /* Create simple products per each option value*/
        /** @var \Magento\Eav\Api\Data\AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options); //remove the first option which is empty

        $associatedProductIds = [];
        $attributeValues = [];
        $i = 1;
        foreach ($options as $option) {
            $taxClassName = self::PRODUCT_TAX_CLASS_1;
            $taxClassId = $this->productTaxClasses[$taxClassName];
            $childSku = $sku . '_child' . $i++;
            $additionalAttributes = [
                self::CONFIGURABLE_ATTRIBUTE_NAME => $option->getValue(),
            ];

            $attributeValues[] = [
                'label' => 'test',
                'attribute_id' => $attribute->getId(),
                'value_index' => $option->getValue(),
            ];

            $associatedProductIds[] = $this->createSimpleProduct($childSku, $price, $taxClassId, $additionalAttributes)->getId();
        }

        if ($this->loadProductBySku($sku)) {
            $product = $this->loadProductBySku($sku);
        } else {
            /** @var $product \Magento\Catalog\Model\Product */
            $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
        }

        $product->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Configurable Product')
            ->setSku($sku)
            ->setTaxClassId($taxClassId)
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1])
            ->setAssociatedProductIds($associatedProductIds)
            ->setConfigurableAttributesData(
                [
                    [
                        'attribute_id' => $attribute->getId(),
                        'attribute_code' => $attribute->getAttributeCode(),
                        'frontend_label' => 'test',
                        'values' => $attributeValues,
                    ],
                ]
            )
            ->save();

        $this->products[$sku] = $product;

        return $product;
    }

    /**
     * Get configurable attribute created earlier
     *
     * @param $attributeName
     * @return bool|\Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected function getConfigurableAttribute($attributeName)
    {
        if (isset($this->configurableAttributes[$attributeName])) {
            return $this->configurableAttributes[$attributeName];
        }
        return false;
    }

    /**
     * Create configurable attribute
     *
     * @see dev/tests/integration/testsuite/Magento/ConfigurableProduct/_files/configurable_attribute.php
     *
     * @param $attributeName
     * @param array $options
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createConfigurableAttribute($attributeName, $options)
    {
        if (isset($this->configurableAttributes[$attributeName])) {
            return $this->configurableAttributes[$attributeName];
        }

        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Eav\Model\Config');
        $attribute = $eavConfig->getAttribute('catalog_product', $attributeName);
        if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
            && $attribute->getId()
        ) {
            $attribute->delete();
        }
        $eavConfig->clear();
        /* Create attribute */
        /** @var $installer \Magento\Catalog\Setup\CategorySetup */
        $installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Setup\CategorySetup');

        /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
        );
        $attribute->setData(
            [
                'attribute_code' => $attributeName,
                'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
                'is_global' => 1,
                'is_user_defined' => 1,
                'frontend_input' => 'select',
                'is_unique' => 0,
                'is_required' => 1,
                'is_searchable' => 0,
                'is_visible_in_advanced_search' => 0,
                'is_comparable' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0,
                'is_used_for_promo_rules' => 0,
                'is_html_allowed_on_front' => 1,
                'is_visible_on_front' => 0,
                'used_in_product_listing' => 0,
                'used_for_sort_by' => 0,
                'frontend_label' => ['Test Configurable'],
                'backend_type' => 'int',
                'option' => $options,
            ]
        );
        $attribute->save();

        /* Assign attribute to attribute set */
        $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());

        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Eav\Model\Config');
        $eavConfig->clear();

        $this->configurableAttributes[$attributeName] = $attribute;

        return $attribute;
    }

    /**
     * Create bundled product and children
     *
     * This file was inspired by
     * @see dev/tests/integration/testsuite/Magento/Bundle/_files/product_with_multiple_options.php
     *
     * @param $sku
     * @param $price
     * @param $taxClassId
     * @param $itemData
     * @return \Magento\Catalog\Model\Product
     */
    protected function createBundledProduct($sku, $price, $taxClassId, $itemData)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $children = [];
        foreach ($itemData['children'] as $child) {
            $taxClassName =
                isset($child['tax_class_name']) ? $child['tax_class_name'] : self::PRODUCT_TAX_CLASS_1;
            $taxClassId = $this->productTaxClasses[$taxClassName];
            $children[$child['sku']] = $this->createSimpleProduct($child['sku'], $child['price'], $taxClassId);
        }


        $bundleOptionsData = $itemData['bundled_options'];

        // Add each child to a group
        $bundleSelectionsData = [];
        foreach ($bundleOptionsData as $optionsKey => $optionsData) {
            $optionGroup = [];
            $optionsKey++;

            $selectedSkus = $optionsData['selected_skus'];
            foreach ($selectedSkus as $selectedSku) {
                $product = $children[$selectedSku];
                $optionGroup[] = [
                    'product_id' => $product->getId(),
                    'selection_qty' => 1, // The qty of this option is set by the ['bundled_options']['qty'] value
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                    'option_id' => $optionsKey
                ];
            }
            if (count($optionGroup)) {
                $bundleSelectionsData[] = $optionGroup;
            }
        }

        $priceType = $itemData['price_type'];

        if ($this->loadProductBySku($sku)) {
            $product = $this->loadProductBySku($sku);
        } else {
            /** @var $product \Magento\Catalog\Model\Product */
            $product = $objectManager->create('Magento\Catalog\Model\Product');
        }

        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE)
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Bundle Product ' . $sku)
            ->setSku($sku)
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setStockData([
                'use_config_manage_stock' => 1,
                'qty' => 100,
                'is_qty_decimal' => 0,
                'is_in_stock' => 1
            ])
            ->setPriceView(1)
            ->setPriceType($priceType)
            ->setPrice($price)
            ->setTaxClassId($taxClassId)
            ->setBundleOptionsData($bundleOptionsData)
            ->setBundleSelectionsData($bundleSelectionsData)
            ->save();

        $this->products[$sku] = $product;

        return $product;
    }

    /**
     * Attempt to load product by SKU
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    protected function loadProductBySku($sku)
    {
        try {
            return $this->productRepository->get($sku);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Create a customer group and associated it with given customer tax class
     *
     * @param int $customerTaxClassId
     * @return int
     */
    protected function createCustomerGroup($customerTaxClassId)
    {
        /** @var \Magento\Customer\Api\GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->objectManager->create('Magento\Customer\Api\GroupRepositoryInterface');
        $customerGroupFactory = $this->objectManager->create('Magento\Customer\Api\Data\GroupInterfaceFactory');
        $customerGroup = $customerGroupFactory->create()
            ->setCode('custom_group')
            ->setTaxClassId($customerTaxClassId);
        $customerGroupId = $groupRepository->save($customerGroup)->getId();
        return $customerGroupId;
    }

    /**
     * Create a customer
     *
     * @param array $customerData
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function createCustomer(array $customerData)
    {
        if ($this->customer) {
            return $this->customer;
        }

        $taxClassName = isset($customerData['tax_class_name'])
            ? $customerData['tax_class_name']
            : self::CUSTOMER_TAX_CLASS_1;
        $customerGroupId = $this->createCustomerGroup($this->customerTaxClasses[$taxClassName]);
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->objectManager->create('Magento\Customer\Model\Customer');
        $customer->isObjectNew(true);
        $customer->setWebsiteId(1)
            ->setEntityTypeId(1)
            ->setAttributeSetId(1)
            ->setEmail('customer@example.com')
            ->setPassword('password')
            ->setGroupId($customerGroupId)
            ->setStoreId(1)
            ->setIsActive(1)
            ->setFirstname('Firstname')
            ->setLastname('Lastname')
            ->save();

        $this->customer = $this->customerRepository->getById($customer->getId());

        return $this->customer;
    }

    /**
     * Create customer address
     *
     * @param array $addressOverride
     * @param int $customerId
     * @return \Magento\Customer\Model\Address
     */
    protected function createCustomerAddress($addressOverride, $customerId)
    {
        $defaultAddressData = [
            'attribute_set_id' => 2,
            'telephone' => 123456789,
            'postcode' => self::MI_POST_CODE,
            'country_id' => self::COUNTRY_US,
            'city' => self::MI_CITY,
            'company' => 'CompanyName',
            'street' => [self::MI_STREET_1],
            'lastname' => 'Smith',
            'firstname' => 'John',
            'parent_id' => 1,
            'region_id' => self::REGION_MI,
        ];
        $addressData = array_merge($defaultAddressData, $addressOverride);

        /** @var \Magento\Customer\Model\Address $customerAddress */
        $customerAddress = $this->objectManager->create('Magento\Customer\Model\Address');
        $customerAddress->setData($addressData)
            ->setCustomerId($customerId)
            ->save();

        return $customerAddress;
    }

    /**
     * Create shopping cart rule
     *
     * @param array $ruleDataOverride
     * @return $this
     */
    protected function createCartRule($ruleDataOverride)
    {
        /** @var \Magento\SalesRule\Model\Rule $salesRule */
        $salesRule = $this->objectManager->create('Magento\SalesRule\Model\Rule');
        $ruleData = array_merge($this->defaultShoppingCartPriceRule, $ruleDataOverride);
        $salesRule->setData($ruleData);
        $salesRule->save();

        return $this;
    }

    /**
     * Create a quote object with customer
     *
     * @param array $quoteData
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return \Magento\Quote\Model\Quote
     */
    protected function createQuote($quoteData, $customer)
    {
        /** @var \Magento\Customer\Api\AddressRepositoryInterface $addressService */
        $addressService = $this->objectManager->create('Magento\Customer\Api\AddressRepositoryInterface');

        /** @var array $shippingAddressOverride */
        $shippingAddressOverride = empty($quoteData['shipping_address']) ? [] : $quoteData['shipping_address'];
        /** @var  \Magento\Customer\Model\Address $shippingAddress */
        $shippingAddress = $this->createCustomerAddress($shippingAddressOverride, $customer->getId());

        /** @var \Magento\Quote\Model\Quote\Address $quoteShippingAddress */
        $quoteShippingAddress = $this->objectManager->create('Magento\Quote\Model\Quote\Address');
        $quoteShippingAddress->importCustomerAddressData($addressService->getById($shippingAddress->getId()));

        /** @var array $billingAddressOverride */
        $billingAddressOverride = empty($quoteData['billing_address']) ? [] : $quoteData['billing_address'];
        /** @var  \Magento\Customer\Model\Address $billingAddress */
        $billingAddress = $this->createCustomerAddress($billingAddressOverride, $customer->getId());

        /** @var \Magento\Quote\Model\Quote\Address $quoteBillingAddress */
        $quoteBillingAddress = $this->objectManager->create('Magento\Quote\Model\Quote\Address');
        $quoteBillingAddress->importCustomerAddressData($addressService->getById($billingAddress->getId()));

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->setStoreId(1)
            ->setIsActive(true)
            ->setIsMultiShipping(false)
            ->assignCustomerWithAddressChange($customer, $quoteBillingAddress, $quoteShippingAddress)
            ->setCheckoutMethod('register')
            ->setPasswordHash($this->accountManagement->getPasswordHash(static::CUSTOMER_PASSWORD));

        if (isset($quoteData['currency_rates'])) {
            $this->createCurrencyRate($quoteData['currency_rates'], $quote);
        }

        return $quote;
    }

    /**
     * Add products to quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $itemsData
     * @return $this
     */
    protected function addProductsToQuote(\Magento\Quote\Model\Quote $quote, $itemsData)
    {
        foreach ($itemsData as $itemData) {
            $sku = $itemData['sku'];
            $price = $itemData['price'];
            $qty = isset($itemData['qty']) ? $itemData['qty'] : 1;
            $taxClassName =
                isset($itemData['tax_class_name']) ? $itemData['tax_class_name'] : self::PRODUCT_TAX_CLASS_1;
            $taxClassId = $this->productTaxClasses[$taxClassName];

            if ($itemData['type'] == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                $product = $this->createBundledProduct($sku, $price, $taxClassId, $itemData);
            } elseif ($itemData['type'] == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $product = $this->createConfigurableProduct($sku, $price, $taxClassId, $itemData);
            } else {
                $product = $this->createSimpleProduct($sku, $price, $taxClassId);
            }
            $this->addProductToQuote($quote, $product, $qty, $itemData);
        }
        return $this;
    }

    /**
     * Add product to quote
     *
     * This file was inspired by
     * @see dev/tests/integration/testsuite/Magento/Checkout/_files/quote_with_bundle_and_options.php
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Catalog\Model\Product $product
     * @param int $qty
     * @param $itemData
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addProductToQuote(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Catalog\Model\Product $product,
        $qty,
        $itemData
    ) {
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            $quote->addProduct($product, $qty);
        } elseif ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {

            $attribute = $this->getConfigurableAttribute(self::CONFIGURABLE_ATTRIBUTE_NAME);

            /** @var \Magento\Eav\Api\Data\AttributeOptionInterface[] $options */
            $options = $attribute->getOptions();
            array_shift($options); //remove the first option which is empty

            $requestInfo = new \Magento\Framework\DataObject;

            if (!empty($options)) {
                $option = $options[0];
                $requestData = [
                    'qty' => $qty
                ];
                /** @var \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface $option */
                $requestData['super_attribute'][$attribute->getId()] = $option->getValue();
                $requestInfo->addData($requestData);
            }

            $quote->addProduct($product, $requestInfo);

        } elseif ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            /** @var $typeInstance \Magento\Bundle\Model\Product\Type */
            //Load options
            $typeInstance = $product->getTypeInstance();
            $typeInstance->setStoreFilter($product->getStoreId(), $product);
            $optionCollection = $typeInstance->getOptionsCollection($product);

            $bundleOptions = [];
            $bundleOptionsQty = [];
            /** @var $option \Magento\Bundle\Model\Option */
            foreach ($optionCollection as $option) {
                $selectionsCollection = $typeInstance->getSelectionsCollection([$option->getId()], $product);
                if ($option->isMultiSelection()) {
                    $selectionsCollection->load();
                    $bundleOptions[$option->getId()] = array_column($selectionsCollection->toArray(), 'selection_id');
                } else {
                    $bundleOptions[$option->getId()] = $selectionsCollection->getFirstItem()->getSelectionId();
                }
                $optionQty = 1;
                foreach ($itemData['bundled_options'] as $bundledOptionData) {
                    if ($option->getTitle() == $bundledOptionData['title']) {
                        $optionQty = $bundledOptionData['qty'];
                        break;
                    }
                }

                $bundleOptionsQty[$option->getId()] = $optionQty;
            }

            $requestInfo = new \Magento\Framework\DataObject(
                [
                    'qty' => $qty,
                    'bundle_option' => $bundleOptions,
                    'bundle_option_qty' => $bundleOptionsQty
                ]
            );

            $quote->addProduct($product, $requestInfo);
        } else {
            throw new \Exception('Unrecognized type: ' . $product->getTypeId());
        }
    }

    /**
     * Create currency rates and set rate on quote
     *
     * @param $ratesData
     * @param \Magento\Quote\Model\Quote $quote
     */
    protected function createCurrencyRate($ratesData, \Magento\Quote\Model\Quote $quote)
    {
        $baseCurrencyCode = $ratesData['base_currency_code'];
        $quoteCurrencyCode = $ratesData['quote_currency_code'];
        $currencyConversionRate = $ratesData['currency_conversion_rate'];

        $newRate = [
            $baseCurrencyCode => [$quoteCurrencyCode => $currencyConversionRate]
        ];
        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $this->objectManager->get('Magento\Directory\Model\Currency');
        $currency->saveRates($newRate);

        // Set the currency code on the store so that the \Magento\Quote\Model\Quote::beforeSave() method sets the
        // quote_currency_code to the appropriate value
        $quote->getStore()->getCurrentCurrency()->setData('currency_code', $quoteCurrencyCode);

        // Save the quote to register the quote_currency_code
        $quote->save();
    }

    /**
     * Create a quote based on given data
     *
     * @param array $quoteData
     * @return \Magento\Quote\Model\Quote
     */
    public function setupQuote($quoteData)
    {
        $customerData = isset($quoteData['customer_data']) ? $quoteData['customer_data'] : [];
        $customer = $this->createCustomer($customerData);

        $quote = $this->createQuote($quoteData, $customer);

        $this->addProductsToQuote($quote, $quoteData['items']);

        //Set shipping amount
        if (isset($quoteData['shipping'])) {
            $shippingMethod = $quoteData['shipping']['method'];
            $shippingAmount = $quoteData['shipping']['amount'];
            $shippingBaseAmount = $quoteData['shipping']['base_amount'];
            $quote->getShippingAddress()->setShippingMethod($shippingMethod)
                ->setShippingDescription('Flat Rate - Fixed')
                ->setShippingAmount($shippingAmount)
                ->setBaseShippingAmount($shippingBaseAmount)
                ->save();

            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        //create shopping cart rules if necessary
        if (!empty($quoteData['shopping_cart_rules'])) {
            foreach ($quoteData['shopping_cart_rules'] as $ruleData) {
                $ruleData['customer_group_ids'] = [$customer->getGroupId()];
                $this->createCartRule($ruleData);
            }
        }

        return $quote;
    }
}
