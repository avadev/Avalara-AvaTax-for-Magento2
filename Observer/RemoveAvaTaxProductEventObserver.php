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
class RemoveAvaTaxProductEventObserver implements ObserverInterface
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
    public $companyId;
    /**
     * @param AvaTaxLogger 
     * @param RestClient $restClient
     * @param HelperConfig $helperConfig
     * @param BatchProcessing $batchProcessing
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        RestClient $restClient,
        HelperConfig $helperConfig,
        BatchProcessing $batchProcessing
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->restClient = $restClient;
        $this->helperConfig = $helperConfig;
        $this->batchProcessing = $batchProcessing;
        $this->companyId = $this->helperConfig->getCompanyId();
    }
    /**
     * Call an AvaTax API to delete synced product 
     * after delete product from Magento
     *
     * @param   Observer $observer
     * @return  $this
     */
    public function execute(Observer $observer)
    {
        if($this->helperConfig->isProductSyncEnabled() && !empty($this->companyId))
        {
            $eventProduct = $observer->getEvent()->getProduct();
            $productId = $eventProduct->getId();
            $itemCode = $eventProduct->getSku();
            if (!empty($productId) && !empty($itemCode)) 
            {
                $client = $this->restClient->getClient();
                $client->withCatchExceptions(false);
                try {
                    $resultObj = $client->deleteCatalogueItem($this->companyId, $itemCode);            
                } catch (\Exception $e) {
                    $success = false;
                    $errorsMsg = $e->getMessage();
                    $this->avaTaxLogger->debug(__('Error while AvaTax sync delete operarion: %1', $errorsMsg), [
                        'request' => 'companyId =>'.$this->companyId,
                        'result' => 'itemCode =>'.$itemCode,
                    ]);
                }      
                $this->batchProcessing->deleteAvaTaxSyncRecords($this->companyId, $itemCode );
            }
        }
        return $this;
    }
}
