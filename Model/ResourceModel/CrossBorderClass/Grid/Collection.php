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

namespace ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\Grid;

use ClassyLlama\AvaTax\Model\Config\Source\CrossBorderClass\Countries;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CountryLink\CollectionFactory as CountryLinkCollectionFactory;
use ClassyLlama\AvaTax\Api\Data\CrossBorderClassRepositoryInterface;
use ClassyLlama\AvaTax\Api\Data\CrossBorderClassInterface;

class Collection extends SearchResult
{
    /**
     * @var CountryLinkCollectionFactory
     */
    protected $countryLinkCollectionFactory;

    /**
     * @var CrossBorderClassRepositoryInterface
     */
    protected $crossBorderClassRepository;

    /**
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param CountryLinkCollectionFactory $countryLinkCollectionFactory
     * @param CrossBorderClassRepositoryInterface $crossBorderClassRepository
     * @param string $mainTable
     * @param string $resourceModel
     *
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        CountryLinkCollectionFactory $countryLinkCollectionFactory,
        CrossBorderClassRepositoryInterface $crossBorderClassRepository,
        $mainTable = 'avatax_cross_border_class',
        $resourceModel = CrossBorderClass::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->countryLinkCollectionFactory = $countryLinkCollectionFactory;
        $this->crossBorderClassRepository = $crossBorderClassRepository;
    }

    /**
     * Fetch associated country information
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $countryLinkCollection = $this->countryLinkCollectionFactory->create();
        $countryLinkCollection->addFieldToFilter('class_id', ['in' => $this->getAllIds()]);

        $countryLinksByClass = [];
        foreach ($countryLinkCollection as $countryLink) {
            if (!(isset($countryLinksByClass[$countryLink->getClassId()]))) {
                $countryLinksByClass[$countryLink->getClassId()] = [];
            }

            $countryLinksByClass[$countryLink->getClassId()][] = $countryLink;
        }

        /**
         * @var CrossBorderClassInterface $item
         */
        foreach ($this->getItems() as $item) {
            $countries = (isset($countryLinksByClass[$item->getId()])) ? $countryLinksByClass[$item->getId()] : [];
            $this->crossBorderClassRepository->addCountriesToClass($item, $countries);
        }

        return $this;
    }

    /**
     * @param string $field
     * @param string $direction
     * @return Collection
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field === 'destination_countries') {
            $field = new \Zend_Db_Expr("GROUP_CONCAT(avatax_cross_border_class_country.country_id)");
            $this->joinCountriesTable();
        }

        return parent::setOrder($field, $direction);
    }

    /**
     * @param array|string $field
     * @param null $condition
     * @return Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'destination_countries') {
            $field = 'avatax_cross_border_class_country.country_id';
            $this->joinCountriesTable();
            if (isset($condition["in"])) {
                foreach ($condition["in"] as $key => $value) {
                    if ($value == Countries::OPTION_VAL_ANY) {
                        $field = [$field, $field];
                        $condition = [$condition, ['null' => true]];
                    }
                }
            }
        }

        /* avoid column ambiguous */
        if ($field === 'class_id') {
            $field = 'main_table.class_id';
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * left join `avatax_cross_border_class_country` table to the collection
     */
    private function joinCountriesTable()
    {
        $this->getSelect()->joinLeft(
            ['avatax_cross_border_class_country' => $this->getTable('avatax_cross_border_class_country')],
            'main_table.class_id = avatax_cross_border_class_country.class_id',
            []
        )->group('main_table.class_id');

    }
}
