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

namespace ClassyLlama\AvaTax\Model\Tax\Sales\Total\Quote\Tax;

use ClassyLlama\AvaTax\Api\Data\ProductCrossBorderDetailsInterface;
use ClassyLlama\AvaTax\Helper\CustomsConfig;
use ClassyLlama\AvaTax\Model\CrossBorderClass\ProductsManagerFactory;
use Magento\Quote\Api\Data\CartItemExtensionInterfaceFactory;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;

class Customs
{
    /**
     * @var CustomsConfig
     */
    protected $customsConfigHelper;

    /**
     * @var ProductsManagerFactory
     */
    protected $crossBorderProductsManagerFactory;

    /** @var CartItemExtensionInterfaceFactory */
    protected $quoteItemExtensionFactory;

    /**
     * @param CustomsConfig                     $customsConfigHelper
     * @param ProductsManagerFactory            $crossBorderProductsManagerFactory
     * @param CartItemExtensionInterfaceFactory $quoteItemExtensionFactory
     */
    public function __construct(
        CustomsConfig $customsConfigHelper,
        ProductsManagerFactory $crossBorderProductsManagerFactory,
        CartItemExtensionInterfaceFactory $quoteItemExtensionFactory
    )
    {
        $this->customsConfigHelper = $customsConfigHelper;
        $this->crossBorderProductsManagerFactory = $crossBorderProductsManagerFactory;
        $this->quoteItemExtensionFactory = $quoteItemExtensionFactory;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return array
     */
    protected function getBorderTypeByItemId($item)
    {
        $crossBorderType = $item->getProduct()->getAvataxCrossBorderType();

        // Set default cross border type
        if (!$crossBorderType) {
            $crossBorderType = $this->customsConfigHelper->getDefaultBorderType();
        }

        // If there are no children, then just set the type and continue
        if (!$item->getHasChildren() || !$item->isChildrenCalculated()) {
            return [$item->getProduct()->getId() => $crossBorderType];
        }

        $productCrossBorderTypes = [];

        // Set the cross border type for each child
        foreach ($item->getChildren() as $childItem) {
            $childCrossBorderType = $childItem->getProduct()->getAvataxCrossBorderType();

            if (!$childCrossBorderType) {
                $childCrossBorderType = $crossBorderType;
            }

            $productCrossBorderTypes[$childItem->getProduct()->getId()] = $childCrossBorderType;
        }

        return $productCrossBorderTypes;
    }

    /**
     * Assign cross border details to quote items
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     *
     * @throws \Magento\Framework\Exception\InputException
     */
    public function assignCrossBorderDetails($shippingAssignment)
    {
        if (!$this->customsConfigHelper->enabled()) {
            return;
        }

        $destinationCountry = $shippingAssignment->getShipping()->getAddress()->getCountryId();
        $productCrossBorderTypes = [];
        $itemsToProcess = [];

        // Gather all items and child items we will add details to, and grab their cross border type by product id
        foreach ($shippingAssignment->getItems() as $item) {
            /** @var \Magento\Quote\Model\Quote\Item $item */

            // Don't process children as we process them from the parent
            if ($item->getParentItem()) {
                continue;
            }

            $productCrossBorderTypes[] = $this->getBorderTypeByItemId($item);

            // Cache the items we already looped through so we can reduce the number of total iterations in this op
            $items = [$item];

            // If we have children we are going to process, replace our items array with them instead
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $items = $item->getChildren();
            }

            $itemsToProcess[] = $items;
        }

        // Create the manager with all our cross border type data
        $crossBorderProductsManager = $this->crossBorderProductsManagerFactory->create(
            [
                'destinationCountry' => $destinationCountry,
                'productCrossBorderTypes' => array_replace(...$productCrossBorderTypes),
            ]
        );

        // Assign cross border details to every item and child items
        foreach (array_merge(...$itemsToProcess) as $item) {
            $this->assignDetailsToItem(
                $item,
                $crossBorderProductsManager->getCrossBorderDetails($item->getProduct()->getId())
            );
        }
    }

    /**
     * @param                                    $item
     * @param ProductCrossBorderDetailsInterface $crossBorderDetails
     */
    protected function assignDetailsToItem($item, $crossBorderDetails)
    {
        $hsCode = null;
        $unitName = null;
        $preferenceProgramIndicator = null;
        $unitAmount = null;

        if (!is_null($crossBorderDetails)) {
            $hsCode = $crossBorderDetails->getHsCode();
            $unitName = $crossBorderDetails->getUnitName();
            $preferenceProgramIndicator = $crossBorderDetails->getPrefProgramIndicator();
            $unitAttributeCode = $crossBorderDetails->getUnitAmountAttrCode();

            if($unitAttributeCode !== '' && $unitAttributeCode !== null) {
                $unitAmount = $item->getProduct()->getData($crossBorderDetails->getUnitAmountAttrCode());
            }
        }

        $quoteItemExtension = $item->getExtensionAttributes();

        if (!$quoteItemExtension) {
            $quoteItemExtension = $this->quoteItemExtensionFactory->create();
        }

        $quoteItemExtension->setHsCode($hsCode);
        $quoteItemExtension->setUnitName($unitName);
        $quoteItemExtension->setPrefProgramIndicator($preferenceProgramIndicator);
        $quoteItemExtension->setUnitAmount($unitAmount);

        $item->setExtensionAttributes($quoteItemExtension);
    }
}
