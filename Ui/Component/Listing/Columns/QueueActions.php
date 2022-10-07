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

namespace ClassyLlama\AvaTax\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

use ClassyLlama\AvaTax\Model\Queue;

/**
 * Class ProductActions
 */
/**
 * @codeCoverageIgnore
 */
class QueueActions extends Column
{
    /**#@+
     * Url path
     */
    const INVOICE_URL_PATH_VIEW = 'sales/invoice/view';
    const CREDITMEMO_URL_PATH_VIEW = 'sales/creditmemo/view';
    /**#@-*/

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['entity_type_code'] == Queue::ENTITY_TYPE_CODE_INVOICE) {
                    $item[$this->getData('name')]['view'] = [

                        'href' => $this->urlBuilder->getUrl(self::INVOICE_URL_PATH_VIEW, ['invoice_id' => $item['entity_id']]),
                        'label' => __('View Invoice')
                    ];
                } elseif ($item['entity_type_code'] == Queue::ENTITY_TYPE_CODE_CREDITMEMO) {
                    $item[$this->getData('name')]['view'] = [

                        'href' => $this->urlBuilder->getUrl(self::CREDITMEMO_URL_PATH_VIEW, ['creditmemo_id' => $item['entity_id']]),
                        'label' => __('View Credit Memo')
                    ];
                } else {

                }
            }
        }

        return $dataSource;
    }
}
