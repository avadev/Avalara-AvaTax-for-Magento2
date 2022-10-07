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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Ui\Component\Listing\Columns;

use ClassyLlama\AvaTax\Api\Data\CrossBorderClassInterface;
use ClassyLlama\AvaTax\Model\CrossBorderTypeRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * @codeCoverageIgnore
 */
class CrossBorderClassActions extends Column
{
    const URL_PATH_VIEW = 'avatax/crossborder_classes/edit';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var CrossBorderTypeRepository
     */
    protected $crossBorderTypeRepository;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        CrossBorderTypeRepository $crossBorderTypeRepository,
        array $components = [],
        array $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->crossBorderTypeRepository = $crossBorderTypeRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {

                /**
                 * overriding cross border type id int with the label
                 */
                if (isset($item[CrossBorderClassInterface::CROSS_BORDER_TYPE])) {
                    try {
                        $item[CrossBorderClassInterface::CROSS_BORDER_TYPE] = $this->fetchCrossBorderTypeValue(
                            $item[CrossBorderClassInterface::CROSS_BORDER_TYPE]
                        );
                    } catch (LocalizedException $e) {
                        $item[CrossBorderClassInterface::CROSS_BORDER_TYPE] = CrossBorderClassInterface::NO_CROSS_BORDER_TYPE_TEXT;
                    }
                }

                $item[$this->getData('name')]['view'] = [

                    'href' => $this->urlBuilder->getUrl(self::URL_PATH_VIEW, ['id' => $item['class_id']]),
                    'label' => __('Edit')
                ];
            }
        }

        return $dataSource;
    }

    /**
     * returns the text value of the Cross Border Type Id
     *
     * @param $typeId
     *
     * @return null|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function fetchCrossBorderTypeValue($typeId)
    {
        return $this->crossBorderTypeRepository->getById($typeId)->getType();
    }
}
