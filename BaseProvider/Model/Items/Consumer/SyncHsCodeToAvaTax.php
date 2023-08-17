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
namespace ClassyLlama\AvaTax\BaseProvider\Model\Items\Consumer;

use ClassyLlama\AvaTax\BaseProvider\Model\Queue\Consumer\DefaultConsumer;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\BaseProvider\Helper\Generic\Config as GenericConfig;
use ClassyLlama\AvaTax\BaseProvider\Helper\Config as QueueConfig;
use ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Queue\CollectionFactory as QueueCollFactory;
use ClassyLlama\AvaTax\Helper\Rest\Config as RestConfig;
use ClassyLlama\AvaTax\Framework\Interaction\Rest as RestClient;
use ClassyLlama\AvaTax\Helper\Config as HelperConfig;
use ClassyLlama\AvaTax\Model\Items\Processing\BatchProcessing;

class SyncHsCodeToAvaTax extends DefaultConsumer
{
    const CLIENT = 'hscode_sync';
    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * @var GenericConfig
     */
    protected $genericConfig;

    /**
     * @var QueueCollFactory
     */
    protected $queueCollFactory;

    /**
     * @var QueueConfig
     */
    protected $queueConfig;

    /**
     * @var RestConfig
     */
    protected $restConfig;

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
     * @var string
     */
    protected $client = self::CLIENT;

    protected $companyId;
    /**
     * @param AvaTaxLogger 
     * @param GenericConfig $genericConfig
     * @param QueueConfig $queueConfig
     * @param QueueCollFactory $queueCollFactory
     * @param RestConfig $restConfig
     * @param RestClient $restClient
     * @param HelperConfig $helperConfig
     * @param BatchProcessing $batchProcessing
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        GenericConfig $genericConfig,
        QueueConfig $queueConfig,
        QueueCollFactory $queueCollFactory,
        RestConfig $restConfig,
        RestClient $restClient,
        HelperConfig $helperConfig,
        BatchProcessing $batchProcessing
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->genericConfig = $genericConfig;
        $this->restConfig = $restConfig;
        $this->restClient = $restClient;
        $this->helperConfig = $helperConfig;
        $this->batchProcessing = $batchProcessing;
        $this->companyId = $this->helperConfig->getCompanyId();
        parent::__construct($avaTaxLogger, $queueConfig, $queueCollFactory);
    }

    /**
     * @inheritDoc
     */
    public function consume(\ClassyLlama\AvaTax\BaseProvider\Api\Data\QueueInterface $queueJob)
    {
        $success = false;
        $response = [];
        $payload = $queueJob->getPayload();
        $payload = json_decode($payload, true);
        $client = $this->restClient->getClient();
        $client->withCatchExceptions(false);
        $body = $payload['body'];
        $companyId = $payload['companyid'];
        if(!empty($companyId) && !empty($body))
        {
            try {
                $responseObj = $client->syncItemCatalogue(
                    $companyId, 
                    $body
                );
                $success = true;
            } catch (\Exception $e) {
                $success = false;
                $errorsMsg = $e->getMessage();
                $this->avaTaxLogger->debug(__('AvaTax items Sync error while SyncHsCodeToAvaTax : %1', $errorsMsg), [
                    'request' => $payload,
                    'result' => $e->getMessage(),
                ]);
            }
            $validatedResponse = $this->validateItemCatalogueResponse($responseObj, $companyId);
            if( isset($validatedResponse['failed']) && count($validatedResponse['failed']) > 0 )
            {
                $successCount = 0; $failedCount = 0;
                $successJson = ''; $failedJson = '';
                if(isset($validatedResponse['success']))
                {
                    $successCount = count($validatedResponse['success']);
                    $successJson = json_encode($validatedResponse['success']);
                }
                if(isset($validatedResponse['failed']))
                {
                    $failedCount = count($validatedResponse['failed']);
                    $failedJson = json_encode($validatedResponse['failed']);
                }
                $this->avaTaxLogger->debug('AvaTax HSCode Sync Logs. Success : ['.$successCount.'] Failed : ['.$failedCount.']', [
                    'success' => $successJson,
                    'failed' => $failedJson,
                ]);
            } 
            return [$success, json_encode($validatedResponse)];
        }else{
            return [$success, ''];
        }
    }
    protected function validateItemCatalogueResponse($response, $companyId)
    {
        $validatedResponse = [];
        if(isset($response->total) && !empty($response->total) && !empty($response->result))
        {
            $i=0;
            foreach($response->result as $item)
            {
                if($item->itemEvent == 'Error')
                {
                    $validatedResponse['failed'][$i]['itemId'] = $item->itemId;
                    $validatedResponse['failed'][$i]['itemCode'] = $item->itemCode;
                    $validatedResponse['failed'][$i]['itemEvent'] = $item->itemEvent;
                    $validatedResponse['failed'][$i]['errors'] = $item->errors;
                } else {
                    $validatedResponse['success'][$i]['itemId'] = $item->itemId;
                    $validatedResponse['success'][$i]['itemCode'] = $item->itemCode;
                    $validatedResponse['success'][$i]['itemEvent'] = $item->itemEvent;
                }
                $i++;
            }
        }
        return $validatedResponse;
    }
}
