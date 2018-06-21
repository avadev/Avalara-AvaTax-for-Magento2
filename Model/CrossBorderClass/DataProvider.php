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

namespace ClassyLlama\AvaTax\Model\CrossBorderClass;

use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\Collection as CrossBorderClassCollection;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CollectionFactory as CrossBorderClassCollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CrossBorderClassCollectionFactory
     */
    protected $crossBorderClassCollectionFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param CrossBorderClassCollectionFactory $crossBorderClassCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CrossBorderClassCollectionFactory $crossBorderClassCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->crossBorderClassCollectionFactory = $crossBorderClassCollectionFactory;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Load and retrieve collection
     *
     * @return CrossBorderClassCollection
     */
    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = $this->crossBorderClassCollectionFactory->create()
                ->addFieldToSelect('*');
        }

        return $this->collection;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $collection = $this->getCollection();
        $items = $collection->getItems();

        /** @var \ClassyLlama\AvaTax\Model\CrossBorderClass $class */
        foreach ($items as $class) {
            $this->loadedData[$class->getId()] = ['crossborder_class' => $class->getData()];
        }

        $data = $this->dataPersistor->get('crossborder_class');
        if (!empty($data)) {
            $this->loadedData[$class['class_id']] = ['crossborder_class' => $data];
            $this->dataPersistor->clear('crossborder_class');
        }

        return $this->loadedData;
    }
}