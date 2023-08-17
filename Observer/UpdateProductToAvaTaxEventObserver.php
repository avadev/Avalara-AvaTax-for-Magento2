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

namespace ClassyLlama\AvaTax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Framework\Interaction\Rest as RestClient;
use ClassyLlama\AvaTax\Helper\Config as HelperConfig;
use ClassyLlama\AvaTax\Model\Items\Processing\BatchProcessing;
use ClassyLlama\AvaTax\Model\Items\Processing\HsCodeBatchProcessing;
class UpdateProductToAvaTaxEventObserver implements ObserverInterface
{
    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var Rest
     */
    protected $restClient;

    /**
     * @var HelperConfig
     */
    protected $helperConfig;

    /**
     * @var BatchProcessing
     */
    protected $batchProcessing;

    /**
     * @var HsCodeBatchProcessing
     */
    protected $hsCodeBatchProcessing;
    
    public $companyId;
    /**
     * @param AvaTaxLogger 
     * @param RestClient $restClient
     * @param HelperConfig $helperConfig
     * @param BatchProcessing $batchProcessing
     * @param HsCodeBatchProcessing $hsCodeBatchProcessing
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        RestClient $restClient,
        HelperConfig $helperConfig,
        BatchProcessing $batchProcessing,
        HsCodeBatchProcessing $hsCodeBatchProcessing
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->restClient = $restClient;
        $this->helperConfig = $helperConfig;
        $this->batchProcessing = $batchProcessing;
        $this->hsCodeBatchProcessing = $hsCodeBatchProcessing;
        $this->companyId = $this->helperConfig->getCompanyId();
    }
    /**
     * Call an AvaTax API to update the synced product 
     * after delete product from Magento
     *
     * @param   Observer $observer
     * @return  mixed
     */
    public function execute(Observer $observer)
    {
        $success = false;
        if($this->helperConfig->isProductSyncEnabled() && !empty($this->companyId))
        {
            $product = $observer->getProduct();
            $data = $product->getData(); 
            $origData = $product->getOrigData(); 
            
            $collection = $this->batchProcessing->getProductCollectionById($data['entity_id']);
            $cross_border_type = $collection->getCustomAttribute('avatax_cross_border_type');

            $updatedProductData = [$data['name'], $data['sku'], $data['tax_class_id'], $data['category_ids'], isset($data['avatax_cross_border_type']) ? $data['avatax_cross_border_type'] : ''];
            $origProductData = [$origData['name'], $origData['sku'], $origData['tax_class_id'], $origData['category_ids'], !empty($cross_border_type) ? $cross_border_type : ''];
            
            if($updatedProductData !== $origProductData)
            {
                $productsSyncCollection = $this->batchProcessing->getProductSyncRecordCollection($data['sku'], $this->companyId);
                $avataxprodid = '';
                if($productsSyncCollection && count($productsSyncCollection->getData()) > 0)
                {
                    $record = $productsSyncCollection->getData();
                    $avataxprodid = $record['avataxprodid'];
                }
                $classificationsData = [];
                if(isset($data['avatax_cross_border_type']) && !empty($data['avatax_cross_border_type']))
                {                    
                    $hsCodeAndCountryCodeData = $this->hsCodeBatchProcessing->getHSCodesWithCountryCodes($data['avatax_cross_border_type']);
                    $classificationsData = $this->hsCodeBatchProcessing->getClassificationsData($hsCodeAndCountryCodeData);
                }
                $payload = $this->batchProcessing->prepareItemCatalogue($product, $avataxprodid, $classificationsData);
                
                $client = $this->restClient->getClient();
                $client->withCatchExceptions(false);
                
                if(isset($record['avataxprodid']) && !empty($record['avataxprodid']))
                {
                    try {
                        $client->syncItemCatalogue($this->companyId, $payload);
                        $this->batchProcessing->markAvaTaxSyncRecordsToCompleted($this->companyId, $record['avataxprodid'], $data['sku']);
                        $success = true;
                    } catch (\Exception $e) {
                        $success = false;
                        $errorsMsg = $e->getMessage();
                        $this->avaTaxLogger->debug(__('AvaTax items update error UpdateAvaTaxProductEventObserver: %1', $errorsMsg), [
                            'request' => json_encode($payload),
                            'result' => $e->getMessage(),
                        ]);
                    }  
                }
                              
                return $success;
            }
        } 
    }
}
