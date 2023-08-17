<?php

namespace ClassyLlama\AvaTax\Model\Items\Processing;

use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Helper\Config as HelperConfig;
use ClassyLlama\AvaTax\Model\Items\Processing\BatchProcessing;
/**
 * Class PendingItemsProcessing
 *
 * @package ClassyLlama\AvaTax\Model\Items\Processing
 */
class PendingItemsProcessing extends AbstractProcessing
    implements ProcessingStrategyInterface
{
     /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;
    /**
     * @var HelperConfig
     */
    protected $helperConfig;
    /**
     * @var BatchProcessing
     */
    protected $batchProcessing;
    
    public $companyId;
    /**
     * PendingItemsProcessing constructor.
     *
     * @param AvaTaxLogger $avaTaxLogger
     * @param HelperConfig $helperConfig
     * @param BatchProcessing $batchProcessing
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        HelperConfig $helperConfig,        
        BatchProcessing $batchProcessing
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->helperConfig = $helperConfig;
        $this->batchProcessing = $batchProcessing;
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
            $pendingSyncProductsSkus = $this->batchProcessing->getPendingSyncProductsSkus();
            
            if(count($pendingSyncProductsSkus)>0)
            {
                $pendingSyncProductsCollection = $this->batchProcessing->getProductCollection($pendingSyncProductsSkus);
                $payloadArr = [];
                if(count($pendingSyncProductsCollection)>0)
                {
                    $avataxIdsWithSkus = array_flip($pendingSyncProductsSkus);
                    $payloadArr = $this->batchProcessing->prepareItemCatalogueRequestPayload($pendingSyncProductsCollection, $avataxIdsWithSkus);
                    $this->batchProcessing->addQueueProcess($payloadArr);
                }
            }            
        }
        return $this;
    }
    
}
