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

namespace ClassyLlama\AvaTax\Model\Items;

use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Model\Queue\Processing\ProcessingStrategyInterface;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\Collection;
use ClassyLlama\AvaTax\Model\ResourceModel\Queue\CollectionFactory;
use ClassyLlama\AvaTax\Model\Queue;
use ClassyLlama\AvaTax\Helper\Config;
use Magento\Framework\DB\Select;
use Zend_Db_Select_Exception;

/**
 * Queue Task
 */
class Task
{

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var CollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * @var int
     */
    protected $processCount = 0;

    /**
     * @var int
     */
    protected $errorCount = 0;

    /**
     * @var array
     */
    protected $errorMessages = [];

    /**
     * @var int
     */
    protected $resetCount = 0;

    /**
     * @var int
     */
    protected $deleteCompleteCount = 0;

    /**
     * @var int
     */
    protected $deleteFailedCount = 0;


    /**
     * @var NewItemsProcessorProvider
     */
    private $newItemsProcessor;
    /**
     * @var PendingItemsProcessorProvider
     */
    private $pendingItemsProcessor;
    /**
     * @var ItemsHsCodeProcessorProvider
     */
    private $itemsHsCodeProcessor;

    /**
     * Task constructor.
     *
     * @param AvaTaxLogger $avaTaxLogger
     * @param Config $avaTaxConfig
     * @param CollectionFactory $queueCollectionFactory
     * @param NewItemsProcessorProvider $newItemsProcessorProvider
     * @param ItemsHsCodeProcessorProvider $itemsHsCodeProcessorProvider
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        Config $avaTaxConfig,
        CollectionFactory $queueCollectionFactory,
        NewItemsProcessorProvider $newItemsProcessorProvider,
        PendingItemsProcessorProvider $pendingItemsProcessorProvider,
        ItemsHsCodeProcessorProvider $itemsHsCodeProcessorProvider
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->newItemsProcessor = $newItemsProcessorProvider->getItemsProcessor();
        $this->pendingItemsProcessor = $pendingItemsProcessorProvider->getItemsProcessor();
        $this->itemsHsCodeProcessor = $itemsHsCodeProcessorProvider->getItemsHsCodeProcessor();        
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * @return int
     */
    public function getProcessCount()
    {
        return $this->processCount;
    }

    /**
     * @return int
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }

    /**
     * @return int
     */
    public function getResetCount()
    {
        return $this->resetCount;
    }

    /**
     * @return int
     */
    public function getDeleteCompleteCount()
    {
        return $this->deleteCompleteCount;
    }

    /**
     * @return int
     */
    public function getDeleteFailedCount()
    {
        return $this->deleteFailedCount;
    }

    /**
     * Entry point for cron job execution of processing the items sync to AvaTax
     */
    public function cronSyncNewItemsToAvaTax()
    {
        if($this->avaTaxConfig->isProductSyncEnabled())
        {
            $this->syncNewItemsToAvaTax();
        }
    }
    /**
     * Entry point for cron job execution of processing the Pending items sync to AvaTax
     */
    public function cronSyncPendingItemsToAvaTax()
    {
        if($this->avaTaxConfig->isProductSyncEnabled())
        {
            $this->syncPendingItemsToAvaTax();
        }
    }
    
    /**
     * Entry point for cron job execution of processing the items hscode sync to AvaTax
     */
    public function cronSyncItemsHsCodeToAvaTax()
    {
        if($this->avaTaxConfig->isProductSyncEnabled())
        {
            $this->syncItemsHsCodeToAvaTax();
        }
    }
    
    /**
     * Process new queue records
     *
     * @param bool $limit
     */
    public function syncNewItemsToAvaTax($limit = false)
    {
        $this->newItemsProcessor->setLimit($limit);
        $this->newItemsProcessor->execute();
        $this->errorMessages = $this->newItemsProcessor->getErrorMessages();
        $this->processCount = $this->newItemsProcessor->getProcessCount();
        $this->errorCount = $this->newItemsProcessor->getErrorCount();
        $context = [
            'error_count'   => $this->errorCount,
            'process_count' => $this->processCount
        ];
        if ($this->getErrorCount() > 0) {
            $context['error_messages'] = implode("\n", $this->getErrorMessages());
        }
    }
    /**
     * Process pending queue records
     *
     * @param bool $limit
     */
    public function syncPendingItemsToAvaTax($limit = false)
    {
        $this->pendingItemsProcessor->setLimit($limit);
        $this->pendingItemsProcessor->execute();
        $this->errorMessages = $this->pendingItemsProcessor->getErrorMessages();
        $this->processCount = $this->pendingItemsProcessor->getProcessCount();
        $this->errorCount = $this->pendingItemsProcessor->getErrorCount();
        $context = [
            'error_count'   => $this->errorCount,
            'process_count' => $this->processCount
        ];
        if ($this->getErrorCount() > 0) {
            $context['error_messages'] = implode("\n", $this->getErrorMessages());
        }
    }
    /**
     * Sync items hscode to AvaTax
     *
     * @param bool $limit
     */
    public function syncItemsHsCodeToAvaTax($limit = false)
    {
        $this->itemsHsCodeProcessor->setLimit($limit);
        $this->itemsHsCodeProcessor->execute();
        $this->errorMessages = $this->itemsHsCodeProcessor->getErrorMessages();
        $this->processCount = $this->itemsHsCodeProcessor->getProcessCount();
        $this->errorCount = $this->itemsHsCodeProcessor->getErrorCount();
        $context = [
            'error_count'   => $this->errorCount,
            'process_count' => $this->processCount
        ];
        if ($this->getErrorCount() > 0) {
            $context['error_messages'] = implode("\n", $this->getErrorMessages());
        }
    }
}
