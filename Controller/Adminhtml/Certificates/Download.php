<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Certificates;

class Download extends \Magento\Backend\App\Action
{
    protected $_publicActions = ['download'];

    /**
     * @var Download
     */
    protected $downloadController;

    /**
     * @param \ClassyLlama\AvaTax\Controller\Certificates\Download $downloadController
     * @param \Magento\Backend\App\Action\Context                  $context
     */
    public function __construct(
        \ClassyLlama\AvaTax\Controller\Certificates\Download $downloadController,
        \Magento\Backend\App\Action\Context $context
    )
    {
        parent::__construct( $context );

        $this->downloadController = $downloadController;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        return $this->downloadController->execute();
    }
}