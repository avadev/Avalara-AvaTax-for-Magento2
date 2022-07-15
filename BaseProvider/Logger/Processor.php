<?php
/*
 * Avalara_BaseProvider
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace ClassyLlama\AvaTax\BaseProvider\Logger;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Injects additional data
 */
/**
 * @codeCoverageIgnore
 */
class Processor
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
