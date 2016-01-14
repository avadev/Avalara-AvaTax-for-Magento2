<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Matt Johnson <matt.johnson@classyllama.com>
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Queue;

use ClassyLlama\AvaTax\Controller\Adminhtml\Queue;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use ClassyLlama\AvaTax\Model\Queue\Task;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;

class Clear extends Queue
{
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
     * Log page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        // Initiate Queue Processing of pending queued entities
        try {
            $this->queueTask->clearQueue();

            if ($this->queueTask->getDeleteCompleteCount() > 0) {
                $message = __('%1 (completed) queued records were cleared. ',
                    $this->queueTask->getDeleteCompleteCount()
                );

                // Display message on the page
                $this->messageManager->addSuccess($message);
            }

            if ($this->queueTask->getDeleteFailedCount() > 0) {
                $message = __('%1 (failed) queued records were cleared. ',
                    $this->queueTask->getDeleteFailedCount()
                );

                // Display message on the page
                $this->messageManager->addSuccess($message);
            }
        } catch (\Exception $e) {

            // Build error message
            $message = __('An error occurred while clearing the queue.');

            // Display error message on the page
            $this->messageManager->addErrorMessage($message . "\n" . __('Error Message: ') . $e->getMessage());

            // Log the exception
            $this->avaTaxLogger->error(
                $message,
                [ /* context */
                    'exception' => sprintf(
                        'Exception message: %s%sTrace: %s',
                        $e->getMessage(),
                        "\n",
                        $e->getTraceAsString()
                    ),
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
