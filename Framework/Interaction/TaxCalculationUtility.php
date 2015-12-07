<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use \Magento\Tax\Api\Data\QuoteDetailsItemInterface;

trait TaxCalculationUtility
{
    /**
     * Item code to Item object array.
     *
     * @var QuoteDetailsItemInterface[]
     */
    protected $keyedItems = [];

    /**
     * Parent item code to children item array.
     *
     * @var QuoteDetailsItemInterface[][]
     */
    protected $parentToChildren;

    public function getKeyedItems()
    {
        return $this->keyedItems;
    }

    public function getChildrenItems($code)
    {
        return isset($this->parentToChildren[$code]) ? $this->parentToChildren[$code] : false;
    }

    /**
     * Computes relationships between items, primarily the child to parent relationship.
     *
     * @param QuoteDetailsItemInterface[] $items
     * @return void
     */
    public function computeRelationships($items)
    {
        $this->keyedItems = [];
        $this->parentToChildren = [];
        foreach ($items as $item) {
            if ($item->getParentCode() === null) {
                $this->keyedItems[$item->getCode()] = $item;
            } else {
                $this->parentToChildren[$item->getParentCode()][] = $item;
            }
        }
    }

    /**
     * Calculates the total quantity for this item.
     *
     * What this really means is that if this is a child item, it return the parent quantity times
     * the child quantity and return that as the child's quantity.
     *
     * @param QuoteDetailsItemInterface $item
     * @return float
     */
    public function getTotalQuantity(\Magento\Tax\Api\Data\QuoteDetailsItemInterface $item)
    {
        if ($item->getParentCode()) {
            $parentQuantity = $this->keyedItems[$item->getParentCode()]->getQuantity();
            return $parentQuantity * $item->getQuantity();
        }
        return $item->getQuantity();
    }
}