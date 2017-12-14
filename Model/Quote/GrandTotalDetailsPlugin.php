<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\Quote;

class GrandTotalDetailsPlugin extends \Magento\Tax\Model\Quote\GrandTotalDetailsPlugin
{
    /**
     * Define constant for tax amount array key
     */
    const KEY_TAX = 'tax';

    /**
     * @var \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionFactory
     */
    protected $grandTotalRatesExtensionFactory;

    /**
     * @var \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory
     */
    protected $ratesFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory
     * @param \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory
     * @param \Magento\Quote\Api\Data\TotalSegmentExtensionFactory $totalSegmentExtensionFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionFactory $grandTotalRatesExtensionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory,
        \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory,
        \Magento\Quote\Api\Data\TotalSegmentExtensionFactory $totalSegmentExtensionFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionFactory $grandTotalRatesExtensionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->grandTotalRatesExtensionFactory = $grandTotalRatesExtensionFactory;
        $this->ratesFactory = $ratesFactory;
        $this->objectManager = $objectManager;
        if (\class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            // Beginning with Magento 2.2 we need to include an additional parameter in the call to parent construct
            $this->loadParentWithJson
            (
                $detailsFactory,
                $ratesFactory,
                $totalSegmentExtensionFactory,
                $taxConfig
            );
        } else {
            // Leaving legacy call for backwards compatibility with Magento 2.1.x
            $this->loadParentWithoutJson
            (
                $detailsFactory,
                $ratesFactory,
                $totalSegmentExtensionFactory,
                $taxConfig
            );
        }
    }

    /**
     * @param array $rates
     * @return array
     */
    protected function getRatesData($rates)
    {
        $taxRates = [];
        foreach ($rates as $rate) {
            $taxRate = $this->ratesFactory->create([]);
            $taxRate->setPercent($rate['percent']);
            $taxRate->setTitle($rate['title']);
            // BEGIN EDIT - Add extension attributes element to array with tax amount
            $extensionAttributes = $this->getExtensionAttributesArray($rate);
            if ($extensionAttributes) {
                $taxRateExtension = $this->grandTotalRatesExtensionFactory->create();
                $taxRateExtension->setTax($rate['extension_attributes']['tax']);
                $taxRate->setExtensionAttributes($taxRateExtension);
            }
            // END EDIT
            $taxRates[] = $taxRate;
        }
        return $taxRates;
    }

    /**
     * @param $rate
     * @return bool
     */
    protected function getExtensionAttributesArray($rate)
    {
        if (isset($rate[\Magento\Framework\Api\ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY][self::KEY_TAX])) {
            return $rate[\Magento\Framework\Api\ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY][self::KEY_TAX];
        }
        return false;
    }

    /**
     * Call through to parent constructor with additional parameter; Magento 2.2.x
     *
     * @param $detailsFactory
     * @param $ratesFactory
     * @param $totalSegmentExtensionFactory
     * @param $taxConfig
     */
    protected function loadParentWithJson
    (
        $detailsFactory,
        $ratesFactory,
        $totalSegmentExtensionFactory,
        $taxConfig
    ) {
        // Load new class(es) via object manager to retain backwards compatibility
        $serializer = $this->objectManager->create('\Magento\Framework\Serialize\Serializer\Json');
        // Call parent constructor with added parameter(s)
        parent::__construct(
            $detailsFactory,
            $ratesFactory,
            $totalSegmentExtensionFactory,
            $taxConfig,
            $serializer
        );
    }

    /**
     * Call through to parent constructor without additional parameter; Magento 2.1.x
     *
     * @param $detailsFactory
     * @param $ratesFactory
     * @param $totalSegmentExtensionFactory
     * @param $taxConfig
     */
    protected function loadParentWithoutJson
    (
        $detailsFactory,
        $ratesFactory,
        $totalSegmentExtensionFactory,
        $taxConfig
    ) {
        // Call parent constructor
        parent::__construct(
            $detailsFactory,
            $ratesFactory,
            $totalSegmentExtensionFactory,
            $taxConfig
        );
    }
}
