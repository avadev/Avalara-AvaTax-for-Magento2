<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Matt Johnson <matt.johnson@classyllama.com>
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model\Logger;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Injects additional AvaTax context in all records
 */
class AvaTaxProcessor
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieves the current Store ID from Magento and adds it to the record
     *
     * @author Matt Johnson <matt.johnson@classyllama.com>
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        // get the store_id and add it to the record
        $store = $this->storeManager->getStore();
        $record['extra']['store_id'] = $store->getId();

        return $record;
    }
}
