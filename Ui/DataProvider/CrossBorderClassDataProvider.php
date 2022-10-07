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

namespace ClassyLlama\AvaTax\Ui\DataProvider;

use ClassyLlama\AvaTax\Api\Data\CrossBorderClassInterface;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\Collection as CrossBorderClassCollection;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CollectionFactory as CrossBorderClassCollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use ClassyLlama\AvaTax\Api\Data\CrossBorderClassRepositoryInterface;

/**
 * @codeCoverageIgnore
 */
class CrossBorderClassDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
     * @var CrossBorderClassRepositoryInterface
     */
    protected $crossBorderClassRepository;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CrossBorderClassCollectionFactory $crossBorderClassCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param CrossBorderClassRepositoryInterface $crossBorderClassRepository
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CrossBorderClassCollectionFactory $crossBorderClassCollectionFactory,
        DataPersistorInterface $dataPersistor,
        CrossBorderClassRepositoryInterface $crossBorderClassRepository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->crossBorderClassCollectionFactory = $crossBorderClassCollectionFactory;
        $this->dataPersistor = $dataPersistor;
        $this->crossBorderClassRepository = $crossBorderClassRepository;
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

        /** @var \ClassyLlama\AvaTax\Model\CrossBorderClass $class */
        foreach ($collection as $class) {
            $this->crossBorderClassRepository->addCountriesToClass($class);

            $countries = $class->getDestinationCountries();
            if (empty($countries)) {
                // Select "any" option if no countries
                $class->setDestinationCountries([\ClassyLlama\AvaTax\Model\Config\Source\CrossBorderClass\Countries::OPTION_VAL_ANY]);
            }

            $this->loadedData[$class->getId()] = $class->getData();
        }

        $data = $this->dataPersistor->get('crossborder_class');
        if (!empty($data)) {
            $this->loadedData[$class['class_id']] = $data;
            $this->dataPersistor->clear('crossborder_class');
        }

        return $this->loadedData;
    }
}