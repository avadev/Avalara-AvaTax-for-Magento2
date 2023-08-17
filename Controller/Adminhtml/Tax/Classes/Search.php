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
namespace ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes;

use ClassyLlama\AvaTax\Model\Search\TaxCode;
use Magento\Backend\Controller\Adminhtml\Index as IndexAction;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Search extends IndexAction implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Tax Code Search module
     *
     * @var TaxCode
     */
    protected $taxCodeSearch;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param TaxCode $taxCodeSearch
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        TaxCode $taxCodeSearch
    ) {
        parent::__construct($context);

        $this->taxCodeSearch = $taxCodeSearch;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Tax Code Search Action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $items = [];

        if (!$this->_authorization->isAllowed('Magento_Backend::tax_class_search')) {
            $items[] = [
                'id' => 'error',
                'type' => __('Error'),
                'name' => __('Access Denied.'),
                'description' => __('You need more permissions to do this.'),
            ];
        } else {
            $start = $this->getRequest()->getParam('start', 1);
            $limit = $this->getRequest()->getParam('limit', 500);
            $query = $this->getRequest()->getParam('query', '');
            $isShippingCode = $this->getRequest()->getParam('shipping_taxcode', '');
            
            $items = $this->taxCodeSearch->setStart(
                $start
            )->setLimit(
                $limit
            )->setQuery(
                $query
            )->setParam(
                $isShippingCode
            )->load()->getResults();            
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($items);
    }
}
