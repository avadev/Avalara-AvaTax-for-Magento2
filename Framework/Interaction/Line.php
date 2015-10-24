<?php

namespace ClassyLlama\AvaTax\Framework\Interaction;

use AvaTax\LineFactory;
use ClassyLlama\AvaTax\Model\Config;

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
        LineFactory $lineFactory
    ) {
        $this->config = $config;
        $this->lineFactory = $lineFactory;
    }

    protected function convertOrderItemToData(\Magento\Sales\Api\Data\OrderItemInterface $data)
    {

    }

    /**
     *
     * TODO: Figure out if we need to account for Streamlined Sales Tax requirements for description
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data
     * @return bool
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
        $data = array_merge(
            [
//                'business_identification_no' => $this->config->getBusinessIdentificationNumber($data['store_id']),
//                'company_code' => $this->config->getCompanyCode($data['store_id']),
//                'detail_level' => DetailLevel::$Document,
//                'doc_type' => DocumentType::$PurchaseInvoice,
//                'origin_address' => $this->address->getAddress($this->config->getOriginAddress($data['store_id'])),
            ],
            $data
        );

        $data = $this->filterDataParams($data);
        $data = $this->validateData($data);
        /** @var $line \AvaTax\Line */
        $line = $this->lineFactory->create();

    }
}