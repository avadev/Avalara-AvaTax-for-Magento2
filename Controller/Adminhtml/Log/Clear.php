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

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Log;

use ClassyLlama\AvaTax\Controller\Adminhtml\Log;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use ClassyLlama\AvaTax\Model\Log\Task;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;

/**
 * @codeCoverageIgnore
 */
class Clear extends Log
{
    /**
     * @var Task
     */
    protected $logTask;

    /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;

    /**
     * Process constructor
     *
     * @param Context $context
     * @param Task $logTask
     * @param AvaTaxLogger $avaTaxLogger
     */
    public function __construct(
        Context $context,
        Task $logTask,
        AvaTaxLogger $avaTaxLogger
    ) {
        $this->logTask = $logTask;
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
        // Initiate Log Clearing
        try {
            $this->logTask->clearLogs();

            if ($this->logTask->getDeleteCount() > 0) {
                $message = __('%1 log records were cleared.',
                    $this->logTask->getDeleteCount()
                );

                // Display message on the page
                $this->messageManager->addSuccess($message);
            } else {
                // Display message on the page
                $this->messageManager->addSuccess(__('No logs needed to be cleared.'));
            }
        } catch (\Exception $e) {

            // Build error message
            $message = __('An error occurred while clearing the log.');

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

        // Redirect browser to log list page
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}
