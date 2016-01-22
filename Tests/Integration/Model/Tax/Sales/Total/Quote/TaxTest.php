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

namespace ClassyLlama\AvaTax\Tests\Integration\Model\Tax\Sales\Total\Quote;

use Magento\Tax\Model\Calculation;
use Magento\TestFramework\Helper\Bootstrap;

require_once __DIR__ . '/SetupUtil.php';
require_once __DIR__ . '/../../../../../_files/tax_calculation_data_aggregated.php';

/**
 * Class TaxTest
 */
class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Utility object for setting up tax rates, tax classes and tax rules
     *
     * @var SetupUtil
     */
    protected $setupUtil = null;

    /**
     * The quote_address fields to ensure match when comparing Magento vs AvaTax tax calculation
     *
     * @var array
     */
    protected $quoteAddressFieldsEnsureMatch = [
        'subtotal',
        'base_subtotal',
        'subtotal_with_discount',
        'base_subtotal_with_discount',
        'tax_amount',
        'base_tax_amount',
        'shipping_amount',
        'base_shipping_amount',
        'shipping_tax_amount',
        'base_shipping_tax_amount',
        'discount_amount',
        'base_discount_amount',
        'grand_total',
        'base_grand_total',
        'shipping_discount_amount',
        'base_shipping_discount_amount',
        'subtotal_incl_tax',
        'base_subtotal_total_incl_tax',
        'discount_tax_compensation_amount',
        'base_discount_tax_compensation_amount',
        'shipping_discount_tax_compensation_amount',
        'base_shipping_discount_tax_compensation_amnt',
        'shipping_incl_tax',
        'base_shipping_incl_tax',
        'gw_base_price',
        'gw_price',
        'gw_items_base_price',
        'gw_items_price',
        'gw_card_base_price',
        'gw_card_price',
        'gw_base_tax_amount',
        'gw_tax_amount',
        'gw_items_base_tax_amount',
        'gw_items_tax_amount',
        'gw_card_base_tax_amount',
        'gw_card_tax_amount',
        'gw_base_price_incl_tax',
        'gw_price_incl_tax',
        'gw_items_base_price_incl_tax',
        'gw_items_price_incl_tax',
        'gw_card_base_price_incl_tax',
        'gw_card_price_incl_tax',
    ];

    /**
     * The quote_address fields to ensure *don't* match when comparing Magento vs AvaTax tax calculation
     *
     * The reason this is important as we need something to test to ensure that we're not accidentally comparing the
     * exact same results, as we don't want false positives.
     *
     * @var array
     */
    protected $quoteAddressFieldsEnsureDiff = [
        'applied_taxes',
    ];

    /**
     * The quote_item fields to ensure match when comparing Magento vs AvaTax tax calculation
     *
     * @var array
     */
    protected $quoteItemFieldsEnsureMatch = [
        'qty',
        'price',
        'base_price',
        'custom_price',
        'discount_percent',
        'discount_amount',
        'base_discount_amount',
        'tax_percent',
        'tax_amount',
        'base_tax_amount',
        'row_total',
        'base_row_total',
        'row_total_with_discount',
        'base_tax_before_discount',
        'tax_before_discount',
        'original_custom_price',
        'base_cost',
        'price_incl_tax',
        'base_price_incl_tax',
        'row_total_incl_tax',
        'base_row_total_incl_tax',
        'discount_tax_compensation_amount',
        'base_discount_tax_compensation_amount',
        'gw_base_price',
        'gw_price',
        'gw_base_tax_amount',
        'gw_tax_amount',
    ];

    /**
     * Verify fields in quote item
     *
     * @param \Magento\Quote\Model\Quote\Address\Item $item
     * @param array $expectedItemData
     * @return $this
     */
    protected function verifyItem($item, $expectedItemData)
    {
        foreach ($expectedItemData as $key => $value) {
            $this->assertEquals($value, $item->getData($key), 'item ' . $key . ' is incorrect');
        }

        return $this;
    }

    /**
     * Verify one tax rate in a tax row
     *
     * @param array $appliedTaxRate
     * @param array $expectedAppliedTaxRate
     * @return $this
     */
    protected function verifyAppliedTaxRate($appliedTaxRate, $expectedAppliedTaxRate)
    {
        foreach ($expectedAppliedTaxRate as $key => $value) {
            $this->assertEquals($value, $appliedTaxRate[$key], 'Applied tax rate ' . $key . ' is incorrect');
        }
        return $this;
    }

    /**
     * Verify one row in the applied taxes
     *
     * @param array $appliedTax
     * @param array $expectedAppliedTax
     * @return $this
     */
    protected function verifyAppliedTax($appliedTax, $expectedAppliedTax)
    {
        foreach ($expectedAppliedTax as $key => $value) {
            if ($key == 'rates') {
                foreach ($value as $index => $taxRate) {
                    $this->verifyAppliedTaxRate($appliedTax['rates'][$index], $taxRate);
                }
            } else {
                $this->assertEquals($value, $appliedTax[$key], 'Applied tax ' . $key . ' is incorrect');
            }
        }
        return $this;
    }

    /**
     * Verify that applied taxes are correct
     *
     * @param array $appliedTaxes
     * @param array $expectedAppliedTaxes
     * @return $this
     */
    protected function verifyAppliedTaxes($appliedTaxes, $expectedAppliedTaxes)
    {
        foreach ($expectedAppliedTaxes as $taxRateKey => $expectedTaxRate) {
            $this->assertTrue(isset($appliedTaxes[$taxRateKey]), 'Missing tax rate ' . $taxRateKey);
            $this->verifyAppliedTax($appliedTaxes[$taxRateKey], $expectedTaxRate);
        }
        return $this;
    }

    /**
     * Verify fields in quote address
     *
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @param array $expectedAddressData
     * @return $this
     */
    protected function verifyQuoteAddress($quoteAddress, $expectedAddressData)
    {
        foreach ($expectedAddressData as $key => $value) {
            if ($key == 'applied_taxes') {
                $this->verifyAppliedTaxes($quoteAddress->getAppliedTaxes(), $value);
            } else {
                $this->assertEquals($value, $quoteAddress->getData($key), 'Quote address ' . $key . ' is incorrect');
            }
        }

        return $this;
    }

    /**
     * Verify fields in quote address and quote item are correct
     *
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @param array $expectedResults
     * @return $this
     */
    protected function verifyResult($quoteAddress, $expectedResults)
    {
        $addressData = $expectedResults['address_data'];

        $this->verifyQuoteAddress($quoteAddress, $addressData);

        $quoteItems = $quoteAddress->getAllItems();
        foreach ($quoteItems as $item) {
            /** @var  \Magento\Quote\Model\Quote\Address\Item $item */
            $sku = $this->getActualSkuForQuoteItem($item);

            $this->assertTrue(
                isset($expectedResults['items_data'][$sku]),
                "Missing array key in 'expected_results' for $sku"
            );

            $expectedItemData = $expectedResults['items_data'][$sku];
            $this->verifyItem($item, $expectedItemData);
        }

        // Make sure all 'expected_result' items are present in quote
        foreach ($quoteItems as $item) {
            unset($expectedResults['items_data'][$this->getActualSkuForQuoteItem($item)]);
        }
        $this->assertEmpty(
            $expectedResults['items_data'],
            'The following expected_results items were not present in quote: '
                . implode(', ', array_keys($expectedResults['items_data']))
        );

        return $this;
    }

    /**
     * Get actual SKU for quote item. This used since configurable product quote items report the child SKU when
     * $item->getProduct()->getSku() is called
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return mixed
     */
    protected function getActualSkuForQuoteItem(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        return $item->getProduct()->getData('sku');
    }

    /**
     * Test tax calculation with various configuration and combination of items
     * This method will test various collectors through $quoteAddress->collectTotals() method
     *
     * @param array $configData
     * @param array $quoteData
     * @param array $expectedResults
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @dataProvider taxDataProvider
     * @return void
     */
    public function testTaxCalculation($configData, $quoteData, $expectedResults)
    {
        /** @var  \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        /** @var  \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector */
        $totalsCollector = $objectManager->create('Magento\Quote\Model\Quote\TotalsCollector');

        //Setup tax configurations
        $this->setupUtil = new SetupUtil($objectManager);
        $this->setupUtil->setupTax($configData);

        $quote = $this->setupUtil->setupQuote($quoteData);
        $quoteAddress = $quote->getShippingAddress();
        $totalsCollector->collectAddressTotals($quote, $quoteAddress);
        $this->verifyResult($quoteAddress, $expectedResults);
    }

    /**
     * Test tax calculation with various configuration and combination of items
     * This method will test various collectors through $quoteAddress->collectTotals() method
     *
     * @param array $configData
     * @param array $quoteData
     * @param array $expectedResults
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @dataProvider taxDataProvider
     * @return void
     */
    public function testNativeVsMagentoTaxCalculation($configData, $quoteData, $expectedResults)
    {
        // Only compare with native Magento taxes if this test is configured to do so
        if (!isset($expectedResults['compare_with_native_tax_calculation'])
            || !$expectedResults['compare_with_native_tax_calculation']
        ) {
            return;
        }

        /** @var  \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        //Setup tax configurations
        $this->setupUtil = new SetupUtil($objectManager);
        // Ensure AvaTax is disabled
        $nativeConfigData = [
            SetupUtil::CONFIG_OVERRIDES => [
                \ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_MODULE_ENABLED => 0,
            ],
        ];
        $nativeQuoteAddress = $this->calculateTaxes($nativeConfigData, $quoteData);
        $avaTaxQuoteAddress = $this->calculateTaxes($configData, $quoteData, false);
        $this->compareResults($nativeQuoteAddress, $avaTaxQuoteAddress, $expectedResults);
    }

    /**
     * Calculate taxes based on the specified config values
     *
     * @param $configData
     * @param $quoteData
     * @param bool $setupTaxData
     * @return \Magento\Quote\Model\Quote\Address
     */
    protected function calculateTaxes($configData, $quoteData, $setupTaxData = true)
    {
        /** @var  \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        /** @var  \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector */
        $totalsCollector = $objectManager->create('Magento\Quote\Model\Quote\TotalsCollector');

        if ($setupTaxData) {
            $this->setupUtil->setupTax($configData);
        } elseif (!empty($configData[SetupUtil::CONFIG_OVERRIDES])) {
            //Tax calculation configuration
            $this->setupUtil->setConfig($configData[SetupUtil::CONFIG_OVERRIDES]);
        }

        $quote = $this->setupUtil->setupQuote($quoteData);
        $quoteAddress = $quote->getShippingAddress();
        $totalsCollector->collectAddressTotals($quote, $quoteAddress);
        return $quoteAddress;
    }

    /**
     * Compare two quote addresses and ensure that their values either match or don't match
     *
     * @param \Magento\Quote\Model\Quote\Address $nativeQuoteAddress
     * @param \Magento\Quote\Model\Quote\Address $avaTaxQuoteAddress
     * @param $expectedResults
     * @return $this
     * @throws \Exception
     */
    protected function compareResults(
        \Magento\Quote\Model\Quote\Address $nativeQuoteAddress,
        \Magento\Quote\Model\Quote\Address $avaTaxQuoteAddress,
        $expectedResults
    ) {
        $this->compareQuoteAddresses($nativeQuoteAddress, $avaTaxQuoteAddress);

        $avaTaxItemsBySku = [];
        foreach ($avaTaxQuoteAddress->getAllItems() as $item) {
            if (isset($avaTaxItemsBySku[$this->getActualSkuForQuoteItem($item)])) {
                throw new \Exception(__('Quote contains items containing the same SKU.'
                    . ' This will not work since SKU must be used as the GUID to compare quote items.'));
            }
            $avaTaxItemsBySku[$this->getActualSkuForQuoteItem($item)] = $item;
        }

        $quoteItems = $nativeQuoteAddress->getAllItems();
        foreach ($quoteItems as $item) {
            /** @var  \Magento\Quote\Model\Quote\Address\Item $item */
            $sku = $this->getActualSkuForQuoteItem($item);

            $this->assertTrue(
                isset($expectedResults['items_data'][$sku]),
                "Missing array key in 'expected_results' for $sku"
            );

            if (!isset($avaTaxItemsBySku[$sku])) {
                throw new \Exception(__('Sku %1 was not found in AvaTax quote.', $sku));
            }

            $avaTaxItem = $avaTaxItemsBySku[$sku];
            $this->compareItems($item, $avaTaxItem);
        }

        // Make sure all 'expected_result' items are present in quote
        foreach ($quoteItems as $item) {
            unset($expectedResults['items_data'][$this->getActualSkuForQuoteItem($item)]);
        }
        $this->assertEmpty(
            $expectedResults['items_data'],
            'The following expected_results items were not present in quote: '
            . implode(', ', array_keys($expectedResults['items_data']))
        );

        return $this;
    }

    /**
     * Compare quote address and ensure fields match / don't match
     *
     * @param \Magento\Quote\Model\Quote\Address $nativeQuoteAddress
     * @param \Magento\Quote\Model\Quote\Address $avaTaxQuoteAddress
     * @return $this
     */
    protected function compareQuoteAddresses($nativeQuoteAddress, $avaTaxQuoteAddress)
    {
        foreach ($this->quoteAddressFieldsEnsureMatch as $value) {
            $this->assertEquals(
                $nativeQuoteAddress->getData($value),
                $avaTaxQuoteAddress->getData($value),
                'native/AvaTax calculation does not match for quote address field: ' . $value
            );
        }
        foreach ($this->quoteAddressFieldsEnsureDiff as $value) {
            $this->assertNotEquals(
                $nativeQuoteAddress->getData($value),
                $avaTaxQuoteAddress->getData($value),
                'native/AvaTax calculation matches (but shouldn\'t be) for quote address field: ' . $value
            );
        }

        return $this;
    }

    /**
     * Compare quote items and ensure fields match
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $nativeItem
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $avaTaxItem
     * @return $this
     */
    protected function compareItems(
        \Magento\Quote\Model\Quote\Item\AbstractItem $nativeItem,
        \Magento\Quote\Model\Quote\Item\AbstractItem $avaTaxItem
    ) {
        foreach ($this->quoteItemFieldsEnsureMatch as $value) {
            $this->assertEquals(
                $nativeItem->getData($value),
                $avaTaxItem->getData($value),
                'native/AvaTax calculation does not match for quote item field: ' . $value
            );
        }

        return $this;
    }

    /**
     * Read the array defined in ../../../../_files/tax_calculation_data_aggregated.php
     * and feed it to testTaxCalculation
     *
     * @return array
     */
    public function taxDataProvider()
    {
        global $taxCalculationData;
        return $taxCalculationData;
    }
}
