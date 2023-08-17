<?php

namespace ClassyLlama\AvaTax\Model\Items\Processing;

use ClassyLlama\AvaTax\Api\BatchQueueTransactionRepositoryInterface;
use ClassyLlama\AvaTax\Api\Data\BatchQueueTransactionInterface;
use ClassyLlama\AvaTax\Api\RestTaxInterface;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Exception;
use ClassyLlama\AvaTax\BaseProvider\Model\Queue\Producer as QueueProducer;
use ClassyLlama\AvaTax\BaseProvider\Model\Items\Consumer\SyncToAvaTax as SyncToAvaTax;
use ClassyLlama\AvaTax\BaseProvider\Model\Items\Consumer\SyncHsCodeToAvaTax as SyncHsCodeToAvaTax;
use ClassyLlama\AvaTax\Helper\Config as HelperConfig;
use ClassyLlama\AvaTax\Model\ProductsSyncFactory as ProductsSyncFactory;
use ClassyLlama\AvaTax\Model\CrossBorderClassFactory as CrossBorderClassFactory;

/**
 * Class BatchProcessing
 *
 * @package ClassyLlama\AvaTax\Model\Items\Processing
 */
class BatchProcessing extends AbstractProcessing
    implements ProcessingStrategyInterface
{
    const BATCH_COLLECTION_PAGE_SIZE = 100;

     /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var QueueProducer
     */
    protected $queueProducer;
    /**
     * @var HelperConfig
     */
    protected $helperConfig;

    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    protected $taxClassRepository;
    /**
     * @var ProductsSyncFactory
     */
    protected $productsSyncFactory;
    /**
     * @var CrossBorderClassFactory
     */
    protected $crossBorderClassFactory;

    public $companyId;
    /**
     * BatchProcessing constructor.
     *
     * @param AvaTaxLogger $avaTaxLogger
     * @param CollectionFactory $productCollectionFactory
     * @param CategoryFactory $categoryFactory
     * @param HelperConfig $helperConfig
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository
     * @param ProductsSyncFactory $productsSyncFactory
     * @param CrossBorderClassFactory $crossBorderClassFactory
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        QueueProducer $queueProducer,
        HelperConfig $helperConfig,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        ProductsSyncFactory $productsSyncFactory,
        CrossBorderClassFactory $crossBorderClassFactory
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->queueProducer = $queueProducer;
        $this->helperConfig = $helperConfig;
        $this->taxClassRepository = $taxClassRepository;
        $this->productsSyncFactory = $productsSyncFactory;
        $this->crossBorderClassFactory = $crossBorderClassFactory;
        $this->companyId = $this->helperConfig->getCompanyId();
    }

    /**
     * Execute Items Sync Batch Processes
     *
     * @return $this
     */
    public function execute()
    {
        if($this->helperConfig->isProductSyncEnabled() && !empty($this->companyId))
        {
            $collection = $this->getProductCollection();
            $payloadArr = [];
            if(count($collection)>0)
            {
                $payloadArr = $this->prepareItemCatalogueRequestPayload($collection);
                $this->addQueueProcess($payloadArr);
            }
        }
        return $this;
    }
    /**
     * Get Product Collections, Filter by Skus
     *
     * @param $skus
     * 
     * @return object
     */
    public function getProductCollection($skus=[])
    {
        $existingProductIds = $this->getExistingSyncProducts();        
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['*']);
        if( 
            ( is_array($skus) && count($skus) == 0 ) && 
            ( is_array($existingProductIds) && count($existingProductIds) > 0 )
        )
        {
            $collection->addFieldToFilter('sku', array('nin' => $existingProductIds));
        }
        if( count($skus) > 0 )
        {
            $collection->addFieldToFilter('sku', array('in' => $skus));
        }
        $collection->setPageSize(self::BATCH_COLLECTION_PAGE_SIZE);
        return $collection;
    }
    /**
     * Get Pending Sync Product Skus
     * 
     * @return array
     */
    public function getPendingSyncProductsSkus()
    {
        $productsSyncFactory = $this->productsSyncFactory->create();
        $productsSyncCollection = $productsSyncFactory->getCollection()
                                ->addFieldToFilter('syncstatus', $this->helperConfig::AVATAX_PRODUCTS_SYNC_PENDING_CODE)
                                ->addFieldToFilter('companyid', $this->companyId)
                                ->setPageSize(self::BATCH_COLLECTION_PAGE_SIZE);
        $pendingSyncProductsSkus = [];
        
        if(is_array($productsSyncCollection->getData()) && count($productsSyncCollection->getData())>0)
        {
            foreach($productsSyncCollection->getData() as $pendingSyncRecord)
            {
                $pendingSyncProductsSkus[$pendingSyncRecord['avataxprodid']] = $pendingSyncRecord['itemcode'];
            }
        }
        return $pendingSyncProductsSkus;
    }
    /**
     * Get Products which are already Synced with AvaTax
     *
     * @return array
     */
    protected function getExistingSyncProducts()
    {
        $productsSyncFactory = $this->productsSyncFactory->create();
        $productsSyncCollection = $productsSyncFactory->getCollection()->addFieldToFilter('companyid', $this->companyId);
        $existingProductIds = [];
        if(is_array($productsSyncCollection->getData()) && count($productsSyncCollection->getData())>0)
        {
            foreach($productsSyncCollection->getData() as $syncRecord)
            {
                $existingProductIds[] = $syncRecord['itemcode'];
            }
        }
        return $existingProductIds;
    }
    /**
     * Prepare ItemCatalogue Request from Product Collection
     *
     * @param $collection
     * @param $avataxIdsWithSkus
     * 
     * @return array
     */
    public function prepareItemCatalogueRequestPayload($collection, $avataxIdsWithSkus=[])
    {
        $payloadArr = [];
        if($collection && !empty($collection))
        {
            $payloadArr['companyid'] = $this->companyId;
            foreach ($collection as $product) 
            {
                $avataxprodid = (isset($avataxIdsWithSkus[$product->getSku()]) && !empty($avataxIdsWithSkus[$product->getSku()])) ? $avataxIdsWithSkus[$product->getSku()] : '';
                $productArr = $this->prepareItemCatalogue($product, $avataxprodid);
                $payloadArr['body'][] = $productArr;
            }
        }        
        return $payloadArr;
    }
    /**
     * Prepare ItemCatalogue, Single Item Array
     *
     * @param $product
     * @param $avataxprodid
     * @param $classificationsData
     * 
     * @return array
     */
    public function prepareItemCatalogue($product, $avataxprodid = '', $classificationsData = [])
    {
        $productArr = [];
        $avataxCode = '';
        $productData = $product->getData();
        $categoryIds = $product->getCategoryIds();
        $objItemGroup = $this->loadCategory(reset($categoryIds));
        $categoryString = $this->getProductCategoryName($categoryIds);
        if(!empty($product->getTaxClassId()))
        {
            $taxClass = $this->taxClassRepository->get($product->getTaxClassId());
            $avataxCode = $taxClass->getAvataxCode();
        }
        if(!empty($avataxprodid))
            $productArr["itemId"] = $avataxprodid;

        $productArr["itemCode"] = $productData['sku'];
        $productArr["description"] = $productData['name'];
        $productArr["summary"] =  $productData['name'];
        $productArr["taxCode"] = $avataxCode;
        $productArr["itemGroup"] = $objItemGroup->getName();
        $productArr["category"] = $categoryString;
        $productArr["source"] = "Magento2_".$this->companyId;
        $productArr["sourceEntityId"] = "Magento2_".$this->companyId;
        if(is_array($classificationsData) && count($classificationsData)>0)
        {
            $i=0;
            foreach ($classificationsData as $cData)
            {
                $productArr["classifications"][$i]['systemCode'] = $cData['systemCode'];
                $productArr["classifications"][$i]['productCode'] = $cData['productCode'];                
                $i++;
            }            
        }
        return $productArr;
    }
    
    /**
     * Add Queue Process Records to BaseProvider Queue Table
     *
     * @param $payloadArr
     * 
     * @return array
     */
    public function addQueueProcess($payloadArr)
    {
        $successRecordCount = 0; $failedRecordCount = 0; $payloadJson = '';
        try{
            $success = true;
            $queueClient  = SyncToAvaTax::CLIENT;
            
            $updateResults = $this->markAvaTaxSyncRecordsToInProgress($payloadArr);
            
            (isset($updateResults['success']) && count($updateResults['success'])) ? $successRecordCount = count($updateResults['success']) : $successRecordCount = 0; 
            (isset($updateResults['failed']) && count($updateResults['failed'])) ? $failedRecordCount = count($updateResults['failed']) : $failedRecordCount = 0; 
            if(isset($updateResults['payload']['body']) && count($updateResults['payload']['body'])>0)
            {
                $payloadJson = json_encode($updateResults['payload']);
                $this->queueProducer->addJob($queueClient, $payloadJson);
            }         
        } catch (\Exception $e) {
            $success = false;
            $errorsMsg = $e->getMessage();
            $this->avaTaxLogger->debug(__('AvaTax items Sync error while addJob: %1', $errorsMsg), [
                'request' => json_encode($payloadArr),
                'result' => $e->getMessage(),
            ]);
            return [$successRecordCount, $failedRecordCount];
        }
        return [$successRecordCount, $failedRecordCount];
    }
    /**
     * Add HsCode Queue Records to BaseProvider Queue Table
     *
     * @param $payloadArr
     * 
     * @return array
     */
    public function addHsCodeSyncQueueProcess($payloadArr)
    {
        $successRecordCount = 0; $failedRecordCount = 0; $payloadJson = '';
        try{
            $success = true;
            $queueClient  = SyncHsCodeToAvaTax::CLIENT;
            $companyId = $payloadArr['companyid'];
            foreach($payloadArr['body'] as $item)
            {
                $itemCode = $item['itemCode'];
                $updateResults = $this->markHsCodeSyncStatusCompleted($companyId, $itemCode);
            }           
            if($updateResults)
            {
                $payloadJson = json_encode($payloadArr);
                $this->queueProducer->addJob($queueClient, $payloadJson);
            }         
        } catch (\Exception $e) {
            $success = false;
            $errorsMsg = $e->getMessage();
            $this->avaTaxLogger->debug(__('AvaTax items Sync error while addHsCodeSyncQueueProcess: %1', $errorsMsg), [
                'request' => json_encode($payloadArr),
                'result' => $e->getMessage(),
            ]);
            return [$successRecordCount, $failedRecordCount];
        }
        return [$successRecordCount, $failedRecordCount];
    }
    /**
     * Mark AvaTax Sync queue entry to InProgress in BaseProvider Queue Table
     *
     * @param $payload
     * 
     * @return array
     */
    public function markAvaTaxSyncRecordsToInProgress($payload)
    {
        $results = [];
        if(count($payload)>0 && count($payload['body'])>0)
        {
            foreach($payload['body'] as $key=>$item)
            {
                $productsSyncCollection = $this->getProductSyncRecordCollection($item['itemCode'], $payload['companyid']);
                if($productsSyncCollection && count($productsSyncCollection->getData()) > 0)
                {
                    $record = $productsSyncCollection->getData();
                    if(isset($record['id']))
                    {
                        try{
                            $this->productsSyncFactory->create()
                            ->load($record['id'])
                            ->setData('syncstatus', $this->helperConfig::AVATAX_PRODUCTS_SYNC_IN_PROGRESS_CODE)
                            ->save();
                        $results['success'][] = $item['itemCode'];
                        } catch (\Exception $e) {
                            $results['failed'][] = $item['itemCode'];
                            unset( $payload['body'][ $key ] );
                        }
                    }        
                } else {
                    try{
                        $productsSync = $this->productsSyncFactory->create();
                        $productsSync->setCompanyid($payload['companyid']);
                        $productsSync->setItemcode($item['itemCode']);
                        $productsSync->setSyncstatus($this->helperConfig::AVATAX_PRODUCTS_SYNC_IN_PROGRESS_CODE);
                        $productsSync->save();
                        $results['success'][] = $item['itemCode'];
                    }catch (\Exception $e) {
                        $results['failed'][] = $item['itemCode'];
                        unset( $payload['body'][ $key ] );
                    }
                }                
            }
        }
        $results['payload'] = $payload;
        return $results;   
    }
    /**
     * Mark HsCode Sync Status record to Completed in BaseProvider Queue Table
     *
     * @param $companyId
     * @param $itemCode
     * 
     * @return bool
     */
    public function markHsCodeSyncStatusCompleted($companyId, $itemCode)
    {
        try{
            $productsSyncCollection = $this->getProductSyncRecordCollection($itemCode, $companyId);
            if($productsSyncCollection && count($productsSyncCollection->getData()) > 0)
            {
                $record = $productsSyncCollection->getData();
                if(isset($record['id']))
                {
                    $result = $this->productsSyncFactory->create()
                        ->load($record['id'])
                        ->setData('hscodesyncstatus', $this->helperConfig::AVATAX_PRODUCTS_HSCODE_SYNC_COMPLETED_CODE)
                        ->save();
                    if($result)
                        return true;
                    else
                        return false;
                }        
            }
            return false;
         }catch (\Exception $e) {
            $errorsMsg = $e->getMessage();
            $this->avaTaxLogger->debug(__('AvaTax items Sync error while markAvaTaxSyncRecordsToCompleted: %1', $errorsMsg), [
                'request' => 'itemCode=>'.$itemCode,
                'result' => '',
            ]);
            return false;
         }  
    }
    /**
     * Mark Mass AvaTax Sync queue entry to Completed in BaseProvider Queue Table
     *
     * @param $response
     * @param $companyId
     * 
     * @return array
     */
    public function massMarkAvaTaxSyncRecordsToCompleted($response, $companyId='')
    {
        $results = [];
        if(count($response) > 0)
        {
            $cid = !empty($companyId) ? $companyId : $this->companyId;
            $i=0;
            foreach($response as $item)
            {
                $this->markAvaTaxSyncRecordsToCompleted($cid, $item['itemId'], $item['itemCode']);
                if($item['itemEvent'] == 'Error')
                {
                    $results['failed'][$i]['itemCode'] = $item['itemCode'];
                    $results['failed'][$i]['itemEvent'] = $item['itemEvent'];
                    $results['failed'][$i]['errors'] = $item['errors'];
                }
                else 
                {
                    $results['success'][$i]['itemCode'] = $item['itemCode'];
                    $results['success'][$i]['itemEvent'] = $item['itemEvent'];
                }
                $i++;
            }
        }
        return $results;   
    }
    /**
     * Mark AvaTax Sync record Pending in BaseProvider Queue Table
     *
     * @param $record
     * 
     * @return bool
     */
    public function markAvaTaxSyncRecordsToPending($record)
    {
        try{
           if($record && count($record) > 0)
            {
                if(isset($record['id']))
                {
                    $result = $this->productsSyncFactory->create()
                        ->load($record['id'])
                        ->setData('syncstatus', $this->helperConfig::AVATAX_PRODUCTS_SYNC_PENDING_CODE)
                        ->save();
                    if($result)
                        return true;
                    else
                        return false;
                }        
            }
            return false;
         }catch (\Exception $e) {
            $errorsMsg = $e->getMessage();
            $this->avaTaxLogger->debug(__('AvaTax items Sync error while markAvaTaxSyncRecordsToPending: %1', $errorsMsg), [
                'request' => 'itemCode=>'.$record['itemcode'],
                'result' => '',
            ]);
            return false;
         }
    }
    /**
     * Mark AvaTax Sync record completed in BaseProvider Queue Table
     *
     * @param $companyId
     * @param $itemId
     * @param $itemCode
     * 
     * @return bool
     */
    public function markAvaTaxSyncRecordsToCompleted($companyId, $itemId, $itemCode)
    {
        try{
            $productsSyncCollection = $this->getProductSyncRecordCollection($itemCode, $companyId);
            if($productsSyncCollection && count($productsSyncCollection->getData()) > 0)
            {
                $record = $productsSyncCollection->getData();
                if(isset($record['id']))
                {
                    $result = $this->productsSyncFactory->create()
                        ->load($record['id'])
                        ->setData('syncstatus', $this->helperConfig::AVATAX_PRODUCTS_SYNC_COMPLETED_CODE)
                        ->setData('avataxprodid', $itemId)
                        ->save();
                    if($result)
                        return true;
                    else
                        return false;
                }        
            }
            return false;
         }catch (\Exception $e) {
            $errorsMsg = $e->getMessage();
            $this->avaTaxLogger->debug(__('AvaTax items Sync error while markAvaTaxSyncRecordsToCompleted: %1', $errorsMsg), [
                'request' => 'itemCode=>'.$itemCode,
                'result' => '',
            ]);
            return false;
         }
    }
    /**
     * Fetch Product Sync Record by companyId and itemCode
     *
     * @param $itemCode
     * @param $companyId
     * 
     * @return mixed
     */
    public function getProductSyncRecordCollection($itemCode, $companyId)
    {
        if(!empty($itemCode) && !empty($companyId))
        {
            $productsSyncCollection = $this->productsSyncFactory->create()->getCollection()
                    ->addFieldToFilter('companyid', $companyId)
                    ->addFieldToFilter('itemcode', $itemCode)
                    ->getFirstItem();
            return $productsSyncCollection;
        }
        return false;
    }
    /**
     * Fetch All Product Sync Record by item Codes and update syncstatus
     *
     * @param $productCodes
     * 
     * @return bool
     */
    public function updateProductSyncRecordSyncStatusesByItemCodes($productCodes)
    {
        if(is_array($productCodes) && count($productCodes)>0)
        {
            $productsSyncCollections = $this->productsSyncFactory->create()->getCollection()
                                        ->addFieldToFilter('itemcode', array('in' => $productCodes));
            foreach($productsSyncCollections as $productsSyncCollection)
            {
                if($productsSyncCollection->getSyncstatus() != $this->helperConfig::AVATAX_PRODUCTS_SYNC_PENDING_CODE)
                {
                    $productsSyncCollection->setSyncstatus($this->helperConfig::AVATAX_PRODUCTS_SYNC_PENDING_CODE);
                    $productsSyncCollection->save();
                }                
            }
            return true;
        }
        return false;
    }
    /**
     * Fetch All Product Sync Record by item Codes and update hscodesyncstatus
     *
     * @param $productCodes
     * 
     * @return bool
     */
    public function updateProductHsCodeSyncStatusesByItemCodes($productCodes)
    {
        if(is_array($productCodes) && count($productCodes)>0)
        {
            $productsSyncCollections = $this->productsSyncFactory->create()->getCollection()
                                        ->addFieldToFilter('itemcode', array('in' => $productCodes));
            foreach($productsSyncCollections as $productsSyncCollection)
            {
                if($productsSyncCollection->getHscodesyncstatus() != $this->helperConfig::AVATAX_PRODUCTS_HSCODE_SYNC_PENDING_CODE)
                {
                    $productsSyncCollection->setHscodesyncstatus($this->helperConfig::AVATAX_PRODUCTS_HSCODE_SYNC_PENDING_CODE);
                    $productsSyncCollection->save();
                }                
            }
            return true;
        }
        return false;
    }
    
    
    /**
     * Delete AvaTax Sync Record from DB Table
     *
     * @param $companyId
     * @param $itemcode
     * 
     * @return bool
     */
    public function deleteAvaTaxSyncRecords( $companyId, $itemcode )
    {
        try 
        {
            $productsSyncCollection = $this->productsSyncFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('companyid', $companyId)
                                ->addFieldToFilter('itemcode', $itemcode)->walk('delete');
            return true;
        } catch (\Exception $e) {
            $errorsMsg = $e->getMessage();
            $this->avaTaxLogger->debug(__('AvaTax items Sync error while deleteAvaTaxSyncRecords: %1', $errorsMsg), [
                'request' => 'recordId=>'.$recordId,
                'result' => 'companyId=>'.$companyId,
            ]);
            return false;
        }   
    }
    /**
     * ReSync Products with new Tax Code when the Tax code is changed
     *
     * @param $taxClassId
     * 
     * @return bool
     */
    public function reSyncProductsWithNewTaxCode($taxClassId)
    {
        if($this->helperConfig->isProductSyncEnabled() && !empty($this->companyId) && !empty($taxClassId))
        {
            $collection = $this->getProductCollectionByTaxClassId($taxClassId);
            $productCodes = [];
            if($collection && count($collection)>0)
            {
                foreach($collection as $product)
                {
                    $productCodes[] = $product->getSku();
                }
                $updateResult = $this->updateProductSyncRecordSyncStatusesByItemCodes($productCodes);
                return $updateResult;
            }  
        }
        return false;
    }
    /**
     * ReSync Products with new HSCode when HSCode or country changed
     *
     * @param $id
     * 
     * @return bool
     */
    public function reSyncProductsWithNewHsCode($id)
    {
        if($this->helperConfig->isProductSyncEnabled() && !empty($this->companyId) && !empty($id))
        {
            $collection = $this->getProductCollectionByCrossBorderClassId($id);
            $productCodes = [];
            if($collection && count($collection)>0)
            {
                foreach($collection as $product)
                {
                    $productCodes[] = $product->getSku();
                }
                $updateResult = $this->updateProductHsCodeSyncStatusesByItemCodes($productCodes);
                return $updateResult;
            } 
        }
        return false;
    }
    
    /**
     * Get products collection by tax class id
     *
     * @param $taxClassId
     * 
     * @return mixed
     */
    public function getProductCollectionByTaxClassId($taxClassId)
    {
        if(!empty($taxClassId))
        {
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect(['sku']);
            $collection->addFieldToFilter('tax_class_id', $taxClassId);
            return $collection;
        }
        return false;
    }
    /**
     * Get products collection by cross border type id
     *
     * @param $id
     * 
     * @return mixed
     */
    public function getProductCollectionByCrossBorderClassId($id)
    {
        if(!empty($id))
        {
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect(['sku']);
            $collection->addAttributeToFilter('avatax_cross_border_type', $id);
            return $collection;
        }
        return false;
    }
    /**
     * Get products collection by product id
     *
     * @param $entityId
     * 
     * @return mixed
     */
    public function getProductCollectionById($entityId)
    {
        if(!empty($entityId))
        {
            $collection = $this->productCollectionFactory->create()
                            ->addAttributeToFilter('entity_id', $entityId)
                            ->getFirstItem();

            return $collection;
        }
        return false;
    }
    
    /**
     * Get Product Catagory Name String from Category Id
     *
     * @param $categoryIds
     * 
     * @return string
     */
    protected function getProductCategoryName($categoryIds) {
        $i=1; $categoryName = '';
        foreach ($categoryIds as $categoryId) {
            $category = $this->loadCategory($categoryId);
            ($i < count($categoryIds)) ? $categoryName .= $category->getName().' > ' : $categoryName .= $category->getName();
            $i++;
        }
        return $categoryName;
    }
    /**
     * Load Category by Category Id
     *
     * @param $categoryId
     * 
     * @return object
     */
    protected function loadCategory($categoryId)
    {
        return $this->categoryFactory->create()->load($categoryId);
    }
}
