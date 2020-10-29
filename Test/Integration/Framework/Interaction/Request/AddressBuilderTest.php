<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Framework\Interaction\Request;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject;
use ClassyLlama\AvaTax\Helper\Rest\Config as AvaTaxHelperRestConfig;

/**
 * Class AddressBuilderTest
 * @package ClassyLlama\AvaTax\Framework\Interaction\Request
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 */
class AddressBuilderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AvaTaxHelperRestConfig
     */
    private $avaTaxHelperRestConfig;

    /**
     * {@inheritDoc}
     */
    protected  function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->avaTaxHelperRestConfig = $this->objectManager->create(AvaTaxHelperRestConfig::class);
    }

    /**
     * - order rollback
     * - create simple product
     * - create order item
     * - create order
     */
    public static function loadFixture()
    {
        include __DIR__ . '/../../../_files/order.php';
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Request\AddressBuilder::build
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadFixture
     */
    public function checkAddressBuilderReturnsCorrectData()
    {
        /** @var Order $order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('123456789');

        /** @var AddressBuilder $addressBuilder */
        $addressBuilder = $this->objectManager->create(AddressBuilder::class);
        $storeId = (int)$this->objectManager->get(StoreManagerInterface::class)->getStore()->getId();

        /** @var array<string, DataObject> $result */
        $result = $addressBuilder->build($order, $storeId);

        /** @var string $shipTo */
        $shipTo = $this->avaTaxHelperRestConfig->getAddrTypeTo();

        self::assertEquals('street', $result[$shipTo]->getData('line_1'));
        self::assertEquals('Los Angeles', $result[$shipTo]->getData('city'));
        self::assertEquals('123-456', $result[$shipTo]->getData('postal_code'));
    }
}
