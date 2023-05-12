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

namespace ClassyLlama\AvaTax\Block\Adminhtml\Log;

use ClassyLlama\AvaTax\Model\ResourceModel\Log\CollectionFactory;
use ClassyLlama\AvaTax\Model\ResourceModel\Log\Collection;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Class Summary
 */
/**
 * @codeCoverageIgnore
 */
class Summary extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $logCollectionFactory;

    /**
     * @var Collection
     */
    protected $logCollection;

    /**
     * @var array
     */
    protected $summaryData;

    /**
     * Summary constructor.
     * @param Context $context
     * @param CollectionFactory $logCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $logCollectionFactory,
        array $data = []
    ) {
        $this->logCollectionFactory = $logCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     */
    protected function getLogCollection()
    {
        // Initialize the log collection
        if ($this->logCollection == null) {
            $this->logCollection = $this->logCollectionFactory->create();
        }
        return $this->logCollection;
    }

    /**
     * @return array
     */
    public function getSummaryData()
    {
        // Initialize the summary data
        if ($this->summaryData == null) {
            $this->summaryData = $this->getLogCollection()->getLevelSummaryCount();
        }
        return $this->summaryData;
    }
}
