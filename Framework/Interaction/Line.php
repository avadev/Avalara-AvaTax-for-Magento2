<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use AvaTax\LineFactory;
use ClassyLlama\AvaTax\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Line
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var LineFactory
     */
    protected $lineFactory = null;

    /**
     * @var ResourceProduct
     */
    protected $resourceProduct = null;

    /**
     * List of types that can be used with setType
     *
     * @var array
     */
    protected $simpleTypes = ['boolean', 'integer', 'string', 'float', 'null'];

    /**
     * A list of valid fields for the data array and meta data about their types to use in validation
     * based on the API documentation.  If any fields are added or removed, the same should be done in getLine.
     *
     * @var array
     */
    protected $validDataFields = [
        'store_id' => ['type' => 'integer'],
        'no' => ['type' => 'string', 'length' => 50, 'required' => true],
        'origin_address' => ['type' => 'object', 'class' => '\AvaTax\Address'],
        'destination_address' => ['type' => 'object', 'class' => '\AvaTax\Address'],
        'item_code' => ['type' => 'string', 'length' => 50],
        'tax_code' => ['type' => 'string', 'length' => 25],
        'customer_usage_type' => ['type' => 'string', 'length' => 25],
        'exemption_no' => ['type' => 'string', 'length' => 25],
        'description' => ['type' => 'string', 'length' => 255],
        'qty' => ['type' => 'float'],
        'amount' => ['type' => 'float', 'required' => true],
        'discounted' => ['type' => 'boolean'],
        'tax_included' => ['type' => 'boolean'],
        'ref1' => ['type' => 'string', 'length' => 250],
        'ref2' => ['type' => 'string', 'length' => 250],
        'tax_override' => ['type' => 'object', 'class' => '\AvaTax\TaxOverride'],
    ];

    public function __construct(
        Config $config,
        LineFactory $lineFactory,
        ResourceProduct $resourceProduct
    ) {
        $this->config = $config;
        $this->lineFactory = $lineFactory;
        $this->resourceProduct = $resourceProduct;
    }

    /**
     * Return an array with relevant data from an order item.
     * TODO: Figure out where tax code comes from
     * TODO: Fields to figure out: customer_usage_type, exemption_no, ref1, ref2, tax_override
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return array
     */
    protected function convertOrderItemToData(\Magento\Sales\Api\Data\OrderItemInterface $item)
    {
        // Items that have parent items do not contain taxable information
        // TODO: Confirm this is true for all item types
        if ($item->getParentItem()) {
            return null;
        }
        $productSkus = $this->resourceProduct->getProductsSku([$item->getProductId()]);
        $productSku = $productSkus[0]['sku'];

        return [
            'store_id' => $item->getStoreId(),
            'no' => $item->getItemId(),
//            'item_code' => $productSku, // TODO: Move this to a more centralized method (maybe static)
//            'tax_code' => null,
//            'customer_usage_type' => null,
//            'exemption_no' => null,
            'description' => $item->getName(),
            'qty' => $item->getQtyOrdered(),
            'amount' => $item->getRowTotal(), // TODO: Figure out what exactly to pass here, look at M1 module
            'discounted' => (bool)($item->getDiscountAmount() > 0),
            'tax_included' => false,
//            'ref1' => null,
//            'ref2' => null,
//            'tax_override' => null,
        ];
    }

    /**
     *
     * TODO: Figure out if we need to account for Streamlined Sales Tax requirements for description
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data
     * @return \AvaTax\Line|null
     */
    public function getLine($data) {
        switch (true) {
            case ($data instanceof \Magento\Sales\Api\Data\OrderItemInterface):
                $data = $this->convertOrderItemToData($data);
                break;
            case ($data instanceof \Magento\Quote\Api\Data\CartItemInterface):
                break;
            case ($data instanceof \Magento\Sales\Api\Data\InvoiceItemInterface):
                break;
            case ($data instanceof \Magento\Sales\Api\Data\CreditmemoItemInterface):
                break;
            case (!is_array($data)):
                return false;
                break;
        }

        if (is_null($data)) {
            return null;
        }
//        $data = array_merge(
//            [
//                'business_identification_no' => $this->config->getBusinessIdentificationNumber($data['store_id']),
//                'company_code' => $this->config->getCompanyCode($data['store_id']),
//                'detail_level' => DetailLevel::$Document,
//                'doc_type' => DocumentType::$PurchaseInvoice,
//                'origin_address' => $this->address->getAddress($this->config->getOriginAddress($data['store_id'])),
//            ],
//            $data
//        );

        $data = $this->filterDataParams($data);
        $data = $this->validateData($data);
        /** @var $line \AvaTax\Line */
        $line = $this->lineFactory->create();

        if (isset($data['no'])) {
            $line->setNo($data['no']);
        }
        if (isset($data['origin_address'])) {
            $line->setOriginAddress($data['origin_address']);
        }
        if (isset($data['destination_address'])) {
            $line->setDestinationAddress($data['destination_address']);
        }
        if (isset($data['item_code'])) {
            $line->setItemCode($data['item_code']);
        }
        if (isset($data['tax_code'])) {
            $line->setTaxCode($data['tax_code']);
        }
        if (isset($data['customer_usage_type'])) {
            $line->setCustomerUsageType($data['customer_usage_type']);
        }
        if (isset($data['exemption_no'])) {
            $line->setExemptionNo($data['exemption_no']);
        }
        if (isset($data['description'])) {
            $line->setDescription($data['description']);
        }
        if (isset($data['qty'])) {
            $line->setQty($data['qty']);
        }
        if (isset($data['amount'])) {
            $line->setAmount($data['amount']);
        }
        if (isset($data['discounted'])) {
            $line->setDiscounted($data['discounted']);
        }
        if (isset($data['tax_included'])) {
            $line->setTaxIncluded($data['tax_included']);
        }
        if (isset($data['ref1'])) {
            $line->setRef1($data['ref1']);
        }
        if (isset($data['ref2'])) {
            $line->setRef2($data['ref2']);
        }
        if (isset($data['tax_override'])) {
            $line->setTaxOverride($data['tax_override']);
        }
        return $line;
    }

    /**
     * Ensures that all required exists and that it is logically valid
     * TODO: Make this do some validating
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data
     * @return mixed
     */
    public function validateData(array $data)
    {
        return $data;
    }

    /**
     * Remove all non-valid fields from data, convert incorrect typed data to the correctly typed data,
     * validate length, and validate existence
     * TODO: Simplify this if possible
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data
     * @return mixed
     * @throws LocalizedException
     */
    protected function filterDataParams(array $data)
    {
        $keys = array_keys($data);
        foreach ($keys as $key) { // TODO: Change this so that it foreaches over data and key is just the key and use item when needing to test the value instead of $data[$key]
            if (!array_key_exists($key, $this->validDataFields) || !isset($this->validDataFields[$key]['type'])) {
                unset($data[$key]);
            } elseif ('array' == $this->validDataFields[$key]['type']) {
                if (gettype($data[$key]) != $this->validDataFields[$key]['type']) {
                    unset($data[$key]);
                    continue;
                }
                if (isset($this->validDataFields[$key]['subtype'])) {
                    foreach ($data[$key] as $subKey => $subItem) {
                        // If the type of each subitem is not correct try to change it (if logical to do so)
                        if (gettype($subItem) != $this->validDataFields[$key]['subtype']['type']) {
                            if (in_array($this->validDataFields[$key]['type'], $this->simpleTypes)) {
                                try {
                                    settype($data[$key], $this->validDataFields[$key]['type']);
                                } catch (\Exception $e) {
                                    throw new LocalizedException(new Phrase('Could not convert "%1[%2]" to a "%3"', [
                                        $key,
                                        $subKey,
                                        $this->validDataFields[$key],
                                    ]));
                                }
                            } else { // Otherwise remove it
                                unset($data[$key][$subKey]);
                                continue;
                            }
                            // If the type of the subitem is correct but is object, enforce that it is of the correct class
                        } elseif ('object' == $this->validDataFields[$key]['subtype']['type'] &&
                            isset($this->validDataFields[$key]['subtype']['class'])) {
                            if (!($subItem instanceof $this->validDataFields[$key]['subtype']['class'])) {
                                unset($data[$key][$subKey]);
                                continue;
                            }
                        }
                    }
                }
            } elseif ('object' == $this->validDataFields[$key]['type'] &&
                isset($this->validDataFields[$key]['class'])) {
                if (!($data[$key] instanceof $this->validDataFields[$key]['class'])) {
                    unset($data[$key]);
                    continue;
                }
            } elseif (gettype($data[$key]) != $this->validDataFields[$key]['type'] &&
                in_array($this->validDataFields[$key]['type'], $this->simpleTypes)) {
                try {
                    settype($data[$key], $this->validDataFields[$key]['type']);
                } catch (\Exception $e) {
                    throw new LocalizedException(new Phrase('Could not convert "%1" to a "%2"', [
                        $key,
                        $this->validDataFields[$key],
                    ]));
                }
            }
        }
        return $data;
    }
}