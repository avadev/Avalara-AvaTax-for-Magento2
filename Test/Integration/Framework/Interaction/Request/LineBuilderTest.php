<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Framework\Interaction\Request;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Framework\DataObject;

/**
 * Class LineBuilderTest
 * @package ClassyLlama\AvaTax\Framework\Interaction\Request
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 */
class LineBuilderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->creditmemoRepository = $this->objectManager->get(CreditmemoRepositoryInterface::class);
    }

    /**
     * - order rollback
     * - create simple product
     * - create order item
     * - create order
     * - create creditmemo
     */
    public static function loadFixture()
    {
        include __DIR__ . '/../../../_files/creditmemo.php';
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Request\LineBuilder::build
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadFixture
     */
    public function checkLineBuilderReturnsCorrectData()
    {
        /** @var Order $order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('123456789');

        $orderItems = [];
        foreach ($order->getItems() as $orderItem) {
            $orderItems[$orderItem->getProductId()] = $orderItem;
        }

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = $this->objectManager->create(FilterBuilder::class);
        $searchCriteriaBuilder->addFilters([
            $filterBuilder->setField('increment_id')
            ->setValue('100000001')
            ->create()
        ]);
        /** @var Creditmemo $creditmemo */
        $creditmemo = current($this->creditmemoRepository->getList($searchCriteriaBuilder->create())->getItems());
        /** @var LineBuilder $lineBuilder */
        $lineBuilder = $this->objectManager->get(LineBuilder::class);

        /** @var array<int, DataObject> $result */
        $lines = $lineBuilder->build($creditmemo, $orderItems);

        self::assertCount(3, $lines);
        self::assertSame('Simple Product', current($lines)->getData('description'));
        self::assertSame(100.0, current($lines)->getData('quantity'));
        self::assertSame('Adjustment refund', next($lines)->getData('description'));
        self::assertSame(-5.0, current($lines)->getData('amount'));
        self::assertSame('Adjustment fee', next($lines)->getData('description'));
        self::assertSame(10.0, current($lines)->getData('amount'));
    }
}
