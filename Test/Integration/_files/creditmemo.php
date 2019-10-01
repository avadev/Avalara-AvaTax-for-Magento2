<?php declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

require 'default_rollback.php';
require 'order.php';

$objectManager = Bootstrap::getObjectManager();

/** @var CreditmemoFactory $creditMemoFactory */
$creditMemoFactory = $objectManager->get(CreditmemoFactory::class);
$creditMemo = $creditMemoFactory->createByOrder($order, $order->getData());
$creditMemo->setOrder($order);
$creditMemo->setState(Creditmemo::STATE_OPEN);
$creditMemo->setIncrementId('100000001');
$creditMemo->setBaseAdjustmentPositive(5.00);
$creditMemo->setBaseAdjustmentNegative(10.00);

/** @var CreditmemoRepositoryInterface $creditMemoRepository */
$creditMemoRepository = $objectManager->get(CreditmemoRepositoryInterface::class);
$creditMemoRepository->save($creditMemo);
