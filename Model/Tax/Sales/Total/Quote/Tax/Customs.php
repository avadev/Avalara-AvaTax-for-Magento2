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
use ClassyLlama\AvaTax\Model\CrossBorderClass\ProductsManager;
use ClassyLlama\AvaTax\Model\CrossBorderClass\ProductsManagerFactory;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\CartItemExtensionInterface;
use Magento\Quote\Api\Data\CartItemExtensionInterfaceFactory;

class Customs
{
    protected $customsConfigHelper;

    protected $crossBorderProductsManagerFactory;

    protected $quoteItemExtensionFactory;

    public function __construct(
        CustomsConfig $customsConfigHelper,
        ProductsManagerFactory $crossBorderProductsManagerFactory,
        CartItemExtensionInterfaceFactory $quoteItemExtensionFactory
    ) {
        $this->customsConfigHelper = $customsConfigHelper;
        $this->crossBorderProductsManagerFactory = $crossBorderProductsManagerFactory;
        $this->quoteItemExtensionFactory = $quoteItemExtensionFactory;
    }

    /**
     * Assign cross border details to quote items
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @throws \Magento\Framework\Exception\InputException
     */
    public function assignCrossBorderDetails($shippingAssignment)
    {
        if (!$this->customsConfigHelper->enabled()) {
            return;
        }

        $destinationCountry = $shippingAssignment->getShipping()->getAddress()->getCountryId();

        $productCrossBorderTypes = [];
        foreach ($shippingAssignment->getItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $parentCrossBorderType = $item->getProduct()->getAvataxCrossBorderType();

                foreach ($item->getChildren() as $childItem) {
                    $crossBorderType = $childItem->getProduct()->getAvataxCrossBorderType();
                    if (!$crossBorderType) {
                        $crossBorderType = $parentCrossBorderType;
                    }

                    if ($crossBorderType) {
                        $productCrossBorderTypes[$childItem->getProduct()->getId()] = $crossBorderType;
                    }
                }
            } elseif ($item->getProduct()->getAvataxCrossBorderType()) {
                $productCrossBorderTypes[$item->getProduct()->getId()] = $item->getProduct()->getAvataxCrossBorderType();
            }
        }

        /**
         * @var ProductsManager $crossBorderProductsManager
         */
        $crossBorderProductsManager = $this->crossBorderProductsManagerFactory->create([
            'destinationCountry' => $destinationCountry,
            'productCrossBorderTypes' => $productCrossBorderTypes,
        ]);

        foreach ($shippingAssignment->getItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $childItem) {
                    $crossBorderDetails = $crossBorderProductsManager->getCrossBorderDetails($childItem->getProduct()->getId());
                    $this->assignDetailsToItem($childItem, $crossBorderDetails);
                }
            } else {
                $crossBorderDetails = $crossBorderProductsManager->getCrossBorderDetails($item->getProduct()->getId());
                $this->assignDetailsToItem($item, $crossBorderDetails);
            }
        }
    }

    /**
     * @param $item
     * @param ProductCrossBorderDetailsInterface $crossBorderDetails
     */
    protected function assignDetailsToItem($item, $crossBorderDetails)
    {
        if (is_null($crossBorderDetails)) {
            return;
        }

        $quoteItemExtension = $item->getExtensionAttributes();

        if (!$quoteItemExtension) {
            $quoteItemExtension = $this->quoteItemExtensionFactory->create();
            $item->setExtensionAttributes($quoteItemExtension);
        }

        $quoteItemExtension->setHsCode($crossBorderDetails->getHsCode());
        $quoteItemExtension->setUnitName($crossBorderDetails->getUnitName());
        $quoteItemExtension->setPrefProgramIndicator($crossBorderDetails->getPrefProgramIndicator());

        $unitAmountAttr = $crossBorderDetails->getUnitAmountAttrCode();
        $quoteItemExtension->setUnitAmount($item->getProduct()->getData($unitAmountAttr));
    }
}