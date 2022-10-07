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

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Queue;

use ClassyLlama\AvaTax\Controller\Adminhtml\Queue;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use ClassyLlama\AvaTax\Model\Queue\Task;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;

/**
 * @codeCoverageIgnore
 */
class Process extends Queue
{
    const BATCH_QUEUE_PROCESSING_LIMIT = 2000;

    /**
     * @var Task
     */
    protected $queueTask;

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * Process constructor
     *
     * @param Context $context
     * @param Task $queueTask
     * @param AvaTaxLogger $avaTaxLogger
     */
    public function __construct(
        Context $context,
        Task $queueTask,
        AvaTaxLogger $avaTaxLogger
    ) {
        $this->queueTask = $queueTask;
        $this->avaTaxLogger = $avaTaxLogger;
        parent::__construct($context);
    }

    /**
     * Process Queue
     *
     * @return Redirect
     */
    public function execute()
    {
        // Initiate Queue Processing of pending queued entities
        try {
            $this->queueTask->processPendingQueue(self::BATCH_QUEUE_PROCESSING_LIMIT);
            $message = __('The queue was successfully processed. ') .
                __('%1 queued records were processed. ', $this->queueTask->getProcessCount());

            $this->messageManager->addSuccess($message);

            if ($this->queueTask->getErrorCount() > 0) {
                $errorMessage = __('Some queue records received errors while processing. ') .
                    __('%1 queued records had errors. ', $this->queueTask->getErrorCount());

                // Include the error messages from the queue task
                foreach ($this->queueTask->getErrorMessages() as $queueErrorMessage) {
                    $errorMessage .= $queueErrorMessage;
                }

                // Display error message on the page
                $this->messageManager->addErrorMessage($errorMessage);
            }

            // Check for any queue records that appear to have been hung and reset them
            $this->queueTask->resetHungQueuedRecords();

            if ($this->queueTask->getResetCount() > 0) {
                $errorMessage = __('Some queue records appeared to have been abandoned while processing. ') .
                    __('%1 queued records were reset to pending so they can be retried. ',
                        $this->queueTask->getResetCount());

                // Display error message on the page
                $this->messageManager->addErrorMessage($errorMessage);
            }

        } catch (\Exception $e) {

            // Build error message
            $message = __('An error occurred while processing the queue. ');
            $partialSuccess = '';
            if ($this->queueTask->getProcessCount() > 0) {
                $partialSuccess = ' ' . __('%1 queued records were processed. ', $this->queueTask->getProcessCount());
            }

            // Display error message on the page
            $this->messageManager->addErrorMessage($message . $partialSuccess . __('Error Message: ')
                . $e->getMessage());

            // Log the exception
            $this->avaTaxLogger->error(
                $message,
                [ /* context */
                  'exception'     => sprintf(
                      'Exception message: %s%sTrace: %s',
                      $e->getMessage(),
                      "\n",
                      $e->getTraceAsString()
                  ),
                  'process_count' => var_export($this->queueTask->getProcessCount(), true),
                ]
            );
        }

        // Redirect browser to queue list page
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');

        return $resultRedirect;
    }
}
