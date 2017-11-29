<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\Quote;

// To maintain backwards compatibility with Magento 2.1.x, we must conditionally process the parent constructor to
// properly include/omit the following new class in the constuctor
if (\class_exists('\Magento\Framework\Serialize\Serializer\Json')) {

    // JSON serializer class exists
    class GrandTotalDetailsPluginAbstract extends \Magento\Tax\Model\Quote\GrandTotalDetailsPlugin
    {
        /**
         * @var \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionFactory
         */
        protected $grandTotalRatesExtensionFactory;

        /**
         * @var \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory
         */
        protected $ratesFactory;

        /**
         * @param \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory
         * @param \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory
         * @param \Magento\Quote\Api\Data\TotalSegmentExtensionFactory $totalSegmentExtensionFactory
         * @param \Magento\Tax\Model\Config $taxConfig
         * @param \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionFactory $grandTotalRatesExtensionFactory
         * @param \Magento\Framework\Serialize\Serializer\Json $serializer
         */
        public function __construct(
            \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory,
            \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory,
            \Magento\Quote\Api\Data\TotalSegmentExtensionFactory $totalSegmentExtensionFactory,
            \Magento\Tax\Model\Config $taxConfig,
            \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionFactory $grandTotalRatesExtensionFactory,
            \Magento\Framework\Serialize\Serializer\Json $serializer
        )
        {
            $this->grandTotalRatesExtensionFactory = $grandTotalRatesExtensionFactory;
            $this->ratesFactory = $ratesFactory;
            // Beginning with Magento 2.2 we need to include an additional parameter in the call to parent construct
            parent::__construct(
                $detailsFactory,
                $ratesFactory,
                $totalSegmentExtensionFactory,
                $taxConfig,
                $serializer
            );
        }
    }
} else {
    // JSON serializer class does not exist
    class GrandTotalDetailsPluginAbstract extends \Magento\Tax\Model\Quote\GrandTotalDetailsPlugin
    {
        /**
         * @var \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionFactory
         */
        protected $grandTotalRatesExtensionFactory;

        /**
         * @var \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory
         */
        protected $ratesFactory;

        /**
         * @param \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory
         * @param \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory
         * @param \Magento\Quote\Api\Data\TotalSegmentExtensionFactory $totalSegmentExtensionFactory
         * @param \Magento\Tax\Model\Config $taxConfig
         * @param \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionFactory $grandTotalRatesExtensionFactory
         */
        public function __construct(
            \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory,
            \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory,
            \Magento\Quote\Api\Data\TotalSegmentExtensionFactory $totalSegmentExtensionFactory,
            \Magento\Tax\Model\Config $taxConfig,
            \ClassyLlama\AvaTax\Api\Data\GrandTotalRatesExtensionFactory $grandTotalRatesExtensionFactory
        )
        {
            $this->grandTotalRatesExtensionFactory = $grandTotalRatesExtensionFactory;
            $this->ratesFactory = $ratesFactory;
            // Leaving original parent construct call for backwards compatibility with Magento 2.1
            parent::__construct(
                $detailsFactory,
                $ratesFactory,
                $totalSegmentExtensionFactory,
                $taxConfig
            );
        }
    }
}
