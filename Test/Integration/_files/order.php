<?php declare(strict_types=1);

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

require 'default_rollback.php';
require 'product_simple.php';
/** @var \Magento\Catalog\Model\Product $product */

/** @var array $addressData */
$addressData = include 'address_data.php';
$objectManager = Bootstrap::getObjectManager();

/** @var OrderAddress $billingAddress */
$billingAddress = $objectManager->create(OrderAddress::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

/** @var OrderAddress $shippingAddress */
$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var Payment $payment */
$payment = $objectManager->create(Payment::class);
$payment->setMethod('checkmo')
    ->setAdditionalInformation('last_trans_id', '1112233')
    ->setAdditionalInformation(
        'metadata',
        [
            'type' => 'free',
            'fraudulent' => false,
        ]
    );

/** @var OrderItem $orderItem */
$orderItem = $objectManager->create(OrderItem::class);
$orderItem->setProductId($product->getId())
    ->setQtyOrdered($product->getQty())
    ->setQtyInvoiced($product->getQty())
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setProductType($product->getTypeId())
    ->setName($product->getName())
    ->setRowTotal($product->getPrice());

/** @var Order $order */
$order = $objectManager->create(Order::class);
$orderData = [
    'increment_id' => '123456789',
    'state' => Order::STATE_PROCESSING,
    'status' => $order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING),
    'grand_total' => 160.00,
    'base_grand_total' => 160.00,
    'subtotal' => 160.00,
    'total_paid' => 160.00,
    'store_id' => $objectManager->get(StoreManagerInterface::class)->getStore()->getId(),
    'website_id' => 1,
    'payment' => $payment
];

$order
    ->setData($orderData)
    ->addItem($orderItem)
    ->setCustomerIsGuest(true)
    ->setCustomerEmail('email@example.com')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
$orderRepository->save($order);
