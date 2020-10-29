<?php declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\CatalogInventory\Model\StockRegistryStorage;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var OrderCollection $orderCollection */
$orderCollection = Bootstrap::getObjectManager()->create(OrderCollection::class);

/** @var Order $order */
foreach ($orderCollection as $order) {
    $order->delete();
}

/** @var StockRegistryStorage $stockRegistryStorage */
$stockRegistryStorage = Bootstrap::getObjectManager()->get(StockRegistryStorage::class);
$stockRegistryStorage->clean();
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
