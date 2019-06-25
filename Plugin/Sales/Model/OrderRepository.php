<?php

namespace ClassyLlama\AvaTax\Plugin\Sales\Model;

use ClassyLlama\AvaTax\Helper\Config;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Tax\Item;
use Magento\Sales\Model\Order\Tax\ItemFactory;
use Magento\Tax\Api\Data\AppliedTaxRateExtension;
use Magento\Tax\Api\Data\AppliedTaxRateInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtension;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterface;
use Magento\Tax\Model\Sales\Order\Tax;
use Magento\Tax\Model\Sales\Order\TaxFactory;

/**
 * Class OrderRepository
 */
class OrderRepository
{
    /**
     * @var TaxFactory
     */
    protected $orderTaxFactory;

    /**
     * @var ItemFactory
     */
    protected $taxItemFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param TaxFactory $orderTaxFactory
     * @param ItemFactory $taxItemFactory
     * @param Config $config
     */
    public function __construct(
        TaxFactory $orderTaxFactory,
        ItemFactory $taxItemFactory,
        Config $config
    ) {
        $this->orderTaxFactory = $orderTaxFactory;
        $this->taxItemFactory = $taxItemFactory;
        $this->config = $config;
    }

    /**
     * Save order tax
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws \Exception
     */
    public function afterSave(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $this->saveOrderTax($order);
        return $order;
    }

    /**
     * @param OrderInterface $order
     * @return $this
     * @throws \Exception
     */
    private function saveOrderTax(OrderInterface $order)
    {
        $extensionAttribute = $order->getExtensionAttributes();
        if (!$extensionAttribute ||
            !$extensionAttribute->getConvertingFromQuote() ||
            $order->getAppliedTaxIsSaved()) {
            return $this;
        }

        /** @var OrderTaxDetailsAppliedTaxInterface[]|array $taxes */
        $taxes = $extensionAttribute->getAppliedTaxes() ? $extensionAttribute->getAppliedTaxes() : [];

        /** @var OrderTaxDetailsItemInterface[]|array $taxesForItems */
        $taxesForItems = $extensionAttribute->getItemAppliedTaxes() ? $extensionAttribute->getAppliedTaxes() : [];

        $ratesIdQuoteItemId = [];
        $isModuleEnabled = $this->isModuleEnabled($order);
        foreach ($taxesForItems as $taxesArray) {
            foreach ($taxesArray['applied_taxes'] as $rates) {
                if (isset($rates['extension_attributes'])) {
                    $taxRates = $rates['extension_attributes'] instanceof OrderTaxDetailsAppliedTaxExtension
                        ? $rates['extension_attributes']->getRates()
                        : $rates['extension_attributes']['rates'];
                    if (is_array($taxRates)) {
                        if (count($taxRates) == 1) {
                            $ratesIdQuoteItemId[$rates['id']][] = [
                                'id' => $taxesArray['item_id'],
                                'percent' => $rates['percent'],
                                'code' => $taxRates[0]['code'],
                                'associated_item_id' => $taxesArray['associated_item_id'],
                                'item_type' => $taxesArray['type'],
                                'amount' => $rates['amount'],
                                'base_amount' => $rates['base_amount'],
                                'real_amount' => $rates['amount'],
                                'real_base_amount' => $rates['base_amount'],
                            ];
                        } else {
                            $sum = 0;
                            foreach ($taxRates as $rate) {
                                $taxAmount = $this->getTaxRateAmount($rate);
                                $sum += $isModuleEnabled ? $taxAmount : $rate['percent'];
                            }

                            foreach ($taxRates as $rate) {
                                $ratio = 0;
                                if ($sum) {
                                    $taxAmount = $this->getTaxRateAmount($rate);
                                    $ratio = ($isModuleEnabled ? $taxAmount : $rate['percent']) / $sum;
                                }
                                $realAmount = $rates['amount'] * $ratio;
                                $realBaseAmount = $rates['base_amount'] * $ratio;
                                $ratesIdQuoteItemId[$rates['id']][] = [
                                    'id' => $taxesArray['item_id'],
                                    'percent' => $rate['percent'],
                                    'code' => $rate['code'],
                                    'associated_item_id' => $taxesArray['associated_item_id'],
                                    'item_type' => $taxesArray['type'],
                                    'amount' => $rates['amount'],
                                    'base_amount' => $rates['base_amount'],
                                    'real_amount' => $realAmount,
                                    'real_base_amount' => $realBaseAmount,
                                ];
                            }
                        }
                    }
                }
            }
        }

        foreach ($taxes as $row) {
            $id = $row['id'];
            if (isset($row['extension_attributes'])) {
                $taxRates = $row['extension_attributes'] instanceof OrderTaxDetailsAppliedTaxExtension
                    ? $row['extension_attributes']->getRates()
                    : $row['extension_attributes']['rates'];
                if (is_array($taxRates)) {
                    foreach ($taxRates as $tax) {
                        if ($row['percent'] == null) {
                            $baseRealAmount = $row['base_amount'];
                        } else {
                            if ($row['percent'] == 0 || $tax['percent'] == 0) {
                                continue;
                            }
                            $baseRealAmount = $row['base_amount'] / $row['percent'] * $tax['percent'];
                            if ($isModuleEnabled) {
                                $baseRealAmount = $this->getTaxRateAmount($tax);
                            }
                        }
                        $hidden = isset($row['hidden']) ? $row['hidden'] : 0;
                        $priority = isset($tax['priority']) ? $tax['priority'] : 0;
                        $position = isset($tax['position']) ? $tax['position'] : 0;
                        $process = isset($row['process']) ? $row['process'] : 0;
                        $data = [
                            'order_id' => $order->getEntityId(),
                            'code' => $tax['code'],
                            'title' => $tax['title'],
                            'hidden' => $hidden,
                            'percent' => $tax['percent'],
                            'priority' => $priority,
                            'position' => $position,
                            'amount' => $row['amount'],
                            'base_amount' => $row['base_amount'],
                            'process' => $process,
                            'base_real_amount' => $baseRealAmount,
                        ];

                        /** @var $orderTax Tax */
                        $orderTax = $this->orderTaxFactory->create();
                        $result = $orderTax->setData($data)->save();

                        if (isset($ratesIdQuoteItemId[$id])) {
                            foreach ($ratesIdQuoteItemId[$id] as $quoteItemId) {
                                if ($quoteItemId['code'] === $tax['code']) {
                                    $itemId = null;
                                    $associatedItemId = null;
                                    if (isset($quoteItemId['id'])) {
                                        //This is a product item
                                        $item = $order->getItemByQuoteItemId($quoteItemId['id']);
                                        if ($item !== null && $item->getId()) {
                                            $itemId = $item->getId();
                                        }
                                    } elseif (isset($quoteItemId['associated_item_id'])) {
                                        //This item is associated with a product item
                                        $item = $order->getItemByQuoteItemId($quoteItemId['associated_item_id']);
                                        $associatedItemId = $item->getId();
                                    }

                                    $data = [
                                        'item_id' => $itemId,
                                        'tax_id' => $result->getTaxId(),
                                        'tax_percent' => $quoteItemId['percent'],
                                        'associated_item_id' => $associatedItemId,
                                        'amount' => $quoteItemId['amount'],
                                        'base_amount' => $quoteItemId['base_amount'],
                                        'real_amount' => $quoteItemId['real_amount'],
                                        'real_base_amount' => $quoteItemId['real_base_amount'],
                                        'taxable_item_type' => $quoteItemId['item_type'],
                                    ];
                                    /** @var $taxItem Item */
                                    $taxItem = $this->taxItemFactory->create();
                                    $taxItem->setData($data)->save();
                                }
                            }
                        }
                    }
                }
            }
        }

        $order->setAppliedTaxIsSaved(true);
        return $this;
    }

    /**
     * Check whether module is enabled
     *
     * @param OrderInterface $order
     * @return bool
     */
    private function isModuleEnabled(OrderInterface $order)
    {
        $storeId = $order->getStoreId();
        $address = $order->getShippingAddress();

        return $this->config->isModuleEnabled($storeId)
            && $this->config->getTaxMode($storeId) != Config::TAX_MODE_NO_ESTIMATE_OR_SUBMIT
            && $this->config->isAddressTaxable($address, $storeId);
    }

    /**
     * Get tax rate amount
     *
     * @param AppliedTaxRateInterface $rate
     * @return float|null
     */
    private function getTaxRateAmount(AppliedTaxRateInterface $rate)
    {
        return $rate['extension_attributes'] instanceof AppliedTaxRateExtension
            ? $rate['extension_attributes']->getTax()
            : $rate['extension_attributes']['tax'];
    }
}
