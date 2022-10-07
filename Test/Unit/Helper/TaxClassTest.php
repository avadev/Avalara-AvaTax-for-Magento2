<?php
/*
 *
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace ClassyLlama\AvaTax\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class TaxClassTest
 * @covers \ClassyLlama\AvaTax\Helper\TaxClass
 * @package \ClassyLlama\AvaTax\Test\Unit\Helper
 */
class TaxClassTest extends TestCase
{
    /**
     * Avatax gift certificate tax code
     */
    const GIFT_CARD_LINE_AVATAX_TAX_CODE = 'PG050000';

    /**
     * UPC Format
     */
    const UPC_FORMAT = 'UPC: %s';

    /**
     * Type code for Gift Card (@see \Magento\GiftCard\Model\Catalog\Product\Type\Giftcard::TYPE_GIFTCARD)
     */
    const PRODUCT_TYPE_GIFTCARD = 'giftcard';

    /**
     * Gift wrapping tax class
     *
     * Copied from \Magento\GiftWrapping\Helper\Data since it is an Enterprise-only module
     */
    const XML_PATH_TAX_CLASS_GIFT_WRAPPING = 'tax/classes/wrapping_tax_class';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig = null;

    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    protected $taxClassRepository;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @var \ClassyLlama\AvaTax\Helper\Config
     */
    protected $config;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \ClassyLlama\AvaTax\Model\GetSkusByProductIds
     */
    private $getSkusByProductIds;

    /**
     * setup
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::__construct
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->taxClassRepository = $this->getMockBuilder(\Magento\Tax\Api\TaxClassRepositoryInterface::class)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->customerGroupRepository = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->config = $this->getMockBuilder(\ClassyLlama\AvaTax\Helper\Config::class)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->productCollectionInstance = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->productInstance = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productInstance->method('getCollection')->willReturn($this->productCollectionInstance);
        $this->productFactory = $this->getMockBuilder(\Magento\Catalog\Model\ProductFactory::class)
        ->disableOriginalConstructor()
        ->getMock();
        $this->productFactory->method('create')->willReturn($this->productInstance);
        
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Model\ProductRepository::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->getSkusByProductIds = $this->getMockBuilder(\ClassyLlama\AvaTax\Model\GetSkusByProductIds::class)
            ->disableOriginalConstructor()
            ->getMock(); 

        $this->taxClass = $this->objectManagerHelper->getObject(
            \ClassyLlama\AvaTax\Helper\TaxClass::class,
            [
              'scopeConfig' => $this->scopeConfig,
              'taxClassRepository' => $this->taxClassRepository,
              'customerGroupRepository' => $this->customerGroupRepository,
              'config' => $this->config,
              'productFactory' => $this->productFactory,
              'productRepository' => $this->productRepository,
              'getSkusByProductIds' => $this->getSkusByProductIds
            ]
        );
        parent::setUp();
    }
    
    /**
     * getAvataxTaxCodeForProduct
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::getAvataxTaxCodeForProduct
     */
    public function testGetAvataxTaxCodeForProductForGiftType()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
               ->disableOriginalConstructor()
               ->getMock();
        $product->expects($this->any())
                ->method('getTypeId')
                ->willReturn(self::PRODUCT_TYPE_GIFTCARD);
        $storeId = 1;
	    $this->assertEquals(self::GIFT_CARD_LINE_AVATAX_TAX_CODE, $this->taxClass->getAvataxTaxCodeForProduct($product, $storeId));
    }

    /**
     * getAvataxTaxCodeForProduct
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::getAvataxTaxCodeForProduct
     */
    public function testGetAvataxTaxCodeForProductForOtherType()
    {
        $product = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product::class,
            []
        );
        $product->setId(1)
                ->setSku("item_1")
                ->setTypeId("simple")
                ->setTaxClassId("TX0001");
        $storeId = 1;
        $skuInput = [1];
        $this->getSkusByProductIds->expects($this->any())
            ->method('execute')
            ->with($skuInput)
            ->willReturn(["1"=>"item_1"]);
        $this->productRepository->expects($this->any())
            ->method('get')
            ->with("item_1")
            ->willReturn($product);
        $taxClass = $this->objectManagerHelper->getObject(
            \Magento\Tax\Model\ClassModel::class,
            []
        );
        $taxClass->setAvataxCode("AVA0001");
        $this->taxClassRepository->expects($this->any())
            ->method('get')
            ->with("TX0001")
            ->willReturn($taxClass);
        
        $this->taxClass->getAvataxTaxCodeForProduct($product, $storeId);
    }

    /**
     * getAvataxTaxCodeForProduct
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::getAvataxTaxCodeForProduct
     */
    public function testGetAvataxTaxCodeForProductForOtherTypeException()
    {
        $product = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product::class,
            []
        );
        $product->setId(1)
                ->setSku("item_1")
                ->setTypeId("simple")
                ->setTaxClassId("TX0001");
        $storeId = 1;
        $skuInput = [1];
        $e = $this->objectManagerHelper->getObject(\Exception::class);
        $this->getSkusByProductIds->expects($this->any())
            ->method('execute')
            ->with($skuInput)
            ->willThrowException($e);
        $this->productRepository->expects($this->any())
            ->method('get')
            ->with("item_1")
            ->willReturn($product);
        $taxClass = $this->objectManagerHelper->getObject(
            \Magento\Tax\Model\ClassModel::class,
            []
        );
        $taxClass->setAvataxCode("AVA0001");
        $this->taxClassRepository->expects($this->any())
            ->method('get')
            ->with("TX0001")
            ->willReturn($taxClass);
        
        $this->taxClass->getAvataxTaxCodeForProduct($product, $storeId);
    }

    /**
     * getAvataxTaxCodeForProduct
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::getAvataxTaxCodeForProduct
     */
    public function testGetAvataxTaxCodeForProductForOtherTypeException2()
    {
        $product = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product::class,
            []
        );
        $product->setId(1)
                ->setSku("item_1")
                ->setTypeId("simple")
                ->setTaxClassId("TX0001");
        $storeId = 1;
        $skuInput = [1];
        $this->getSkusByProductIds->expects($this->any())
            ->method('execute')
            ->with($skuInput)
            ->willReturn(["1"=>"item_1"]);
        $this->productRepository->expects($this->any())
            ->method('get')
            ->with("item_1")
            ->willReturn($product);
        $taxClass = $this->objectManagerHelper->getObject(
            \Magento\Tax\Model\ClassModel::class,
            []
        );
        $taxClass->setAvataxCode("AVA0001");
        $e = $this->objectManagerHelper->getObject(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->taxClassRepository->expects($this->any())
            ->method('get')
            ->with("TX0001")
            ->willThrowException($e);
        
        $this->taxClass->getAvataxTaxCodeForProduct($product, $storeId);
    }

    /**
     * getItemCodeOverride
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::getItemCodeOverride
     */
    public function testGetItemCodeOverride()
    {
        $product = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product::class,
            []
        );
        $product->setId(1)
                ->setSku("item_1")
                ->setTypeId("simple")
                ->setTaxClassId("TX0001");
        $this->config->expects($this->any())
                ->method('getUpcAttribute')
                ->willReturn('sku');
	    $this->taxClass->getItemCodeOverride($product);

        $product->setSku("");
	    $this->taxClass->getItemCodeOverride($product);
    }

    /**
     * getRef1ForProduct
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::getRef1ForProduct
     */
    public function testGetRef1ForProduct()
    {
        $product = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product::class,
            []
        );
        $product->setId(1)
                ->setSku("item_1")
                ->setTypeId("simple")
                ->setTaxClassId("TX0001");
        $this->config->expects($this->any())
                ->method('getRef1Attribute')
                ->willReturn('sku');
	    $this->taxClass->getRef1ForProduct($product);

        $product->setSku("");
	    $this->taxClass->getRef1ForProduct($product);
    }

    /**
     * getRef2ForProduct
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::getRef2ForProduct
     */
    public function testGetRef2ForProduct()
    {
        $product = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product::class,
            []
        );
        $product->setId(1)
                ->setSku("item_1")
                ->setTypeId("simple")
                ->setTaxClassId("TX0001");
        $this->config->expects($this->any())
                ->method('getRef2Attribute')
                ->willReturn('sku');
	    $this->taxClass->getRef2ForProduct($product);

        $product->setSku("");
	    $this->taxClass->getRef2ForProduct($product);
    }

    /**
     * getAvataxTaxCodeForShipping
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::getAvataxTaxCodeForShipping
     */
    public function testGetAvataxTaxCodeForShipping()
    {
        $this->config->expects($this->any())
                ->method('getShippingTaxCode')
                ->willReturn('SH0001');
	    $this->taxClass->getAvataxTaxCodeForShipping();
    }

    /**
     * getAvataxTaxCodeForGiftOptions
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::getAvataxTaxCodeForGiftOptions
     */
    public function testGetAvataxTaxCodeForGiftOptions()
    {
        $store = null;
        $this->scopeConfig->expects($this->any())
                ->method('getValue')
                ->willReturn('GFT0001');
        $taxClass = $this->objectManagerHelper->getObject(
            \Magento\Tax\Model\ClassModel::class,
            []
        );
        $taxClass->setAvataxCode("GFT0001");
        $this->taxClassRepository->expects($this->any())
            ->method('get')
            ->with("GFT0001")
            ->willReturn($taxClass);
	    $this->taxClass->getAvataxTaxCodeForGiftOptions($store);
    }

    /**
     * getAvataxTaxCodeForCustomer
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::getAvataxTaxCodeForCustomer
     */
    public function testGetAvataxTaxCodeForCustomer()
    {
        $customer = $this->objectManagerHelper->getObject(
            \Magento\Customer\Model\Data\Customer::class,
            []
        );
	    $this->taxClass->getAvataxTaxCodeForCustomer($customer);
        $customer->setGroupId(1);
        $customerGroup = $this->objectManagerHelper->getObject(
            \Magento\Customer\Model\Data\Group::class,
            []
        );
        $customerGroup->setTaxClassId("CUST0001");
        $this->customerGroupRepository->expects($this->any())
            ->method('getById')
            ->with(1)
            ->willReturn($customerGroup);
        $taxClass = $this->objectManagerHelper->getObject(
            \Magento\Tax\Model\ClassModel::class,
            []
        );
        $taxClass->setAvataxCode("AVA0001");
        $this->taxClassRepository->expects($this->any())
            ->method('get')
            ->with("CUST0001")
            ->willReturn($taxClass);
        $this->taxClass->getAvataxTaxCodeForCustomer($customer);
    }

    /**
     * getAvataxTaxCodeForCustomer
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::getAvataxTaxCodeForCustomer
     */
    public function testGetAvataxTaxCodeForCustomerForException()
    {
        $customer = $this->objectManagerHelper->getObject(
            \Magento\Customer\Model\Data\Customer::class,
            []
        );
        $customer->setGroupId(1);
        $e = $this->objectManagerHelper->getObject(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->customerGroupRepository->expects($this->any())
            ->method('getById')
            ->willThrowException($e);
        $this->taxClass->getAvataxTaxCodeForCustomer($customer);
    }

    /**
     * populateCorrectTaxClasses
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\TaxClass::populateCorrectTaxClasses
     */
    public function testPopulateCorrectTaxClasses()
    {
        $items = [];
        $storeId = 1;

        $product = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product::class,
            []
        );
        $product->setTaxClassId("AVA0001");
        $orderItem = $this->objectManagerHelper->getObject(
            \Magento\Sales\Model\Order\Item::class,
            []
        );
        $orderItem->setProductId(1);
        $product->setId(1);
        $orderItem->setProduct($product);

        $invoiceItem1 = $this->objectManagerHelper->getObject(
            \Magento\Sales\Model\Order\Invoice\Item::class,
            []
        );
        $invoiceItem1->setOrderItem($orderItem);

        $invoiceItem2 = $this->objectManagerHelper->getObject(
            \Magento\Sales\Model\Order\Invoice\Item::class,
            []
        );
        $invoiceItem2->setParentItem($invoiceItem1);
        $orderItem->setProductId(3);
        $product->setId(3);
        $orderItem->setProduct($product);
        $invoiceItem2->setOrderItem($orderItem);

        $invoiceItem3 = $this->objectManagerHelper->getObject(
            \Magento\Sales\Model\Order\Invoice\Item::class,
            []
        );
        $orderItem->setProductId(2);
        $product->setId(2);
        $orderItem->setProduct($product);
        $invoiceItem3->setOrderItem($orderItem);
        $items = [$invoiceItem1, $invoiceItem2, $invoiceItem3];


        $this->productCollectionInstance->expects($this->any())
            ->method('addAttributeToSelect')
            ->willReturn($this->productCollectionInstance);
        $this->productCollectionInstance->expects($this->any())
            ->method('addStoreFilter')
            ->willReturn($this->productCollectionInstance);

        $products = [];
        $product = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product::class,
            []
        );
        $product->setId(1);
        $product->setTaxClassId("AVA0001");
        $products[] = $product;
        $product->setId(2);
        $products[] = $product;
        $this->productCollectionInstance->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturn($products);
        $this->taxClass->populateCorrectTaxClasses($items, $storeId);
    }
}
