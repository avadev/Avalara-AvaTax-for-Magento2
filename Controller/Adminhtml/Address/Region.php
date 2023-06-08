<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */
namespace ClassyLlama\AvaTax\Controller\Adminhtml\Address;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;

/**
 * Regions controller
 */
/**
 * @codeCoverageIgnore
 */
class Region extends \Magento\Framework\App\Action\Action
{
    /**
     * @var array
     */
    private $regions;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var DirectoryHelper
     */
    private $directoryHelper;

    /**
     * @param DirectoryHelper $directoryHelper
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Escaper $escaper
     */
    public function __construct(
        DirectoryHelper $directoryHelper,
        Context $context,
        JsonFactory $resultJsonFactory,
        Escaper $escaper = null
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->escaper = $escaper;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store');
        $resultJson->setHeader('Pragma', 'no-cache');
        if (!$this->regions) {
            try {
                $this->regions = $this->directoryHelper->getRegionData();
            } catch (\Exception $e) {
                $resultJson->setStatusHeader(
                    \Zend\Http\Response::STATUS_CODE_400,
                    \Zend\Http\AbstractMessage::VERSION_11,
                    'Bad Request'
                );
                $this->regions = ['message' => $this->escaper->escapeHtml($e->getMessage())];
            }
        }

        return $resultJson->setData($this->regions);
    }
}
