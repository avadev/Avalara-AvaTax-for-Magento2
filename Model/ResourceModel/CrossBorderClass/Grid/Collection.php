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

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CountryLink\Collection as CountryLinkCollection;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CountryLink\CollectionFactory as CountryLinkCollectionFactory;
use ClassyLlama\AvaTax\Api\Data\CrossBorderClassRepositoryInterface;
use ClassyLlama\AvaTax\Api\Data\CrossBorderClassInterface;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        CountryLinkCollectionFactory $countryLinkCollectionFactory,
        CrossBorderClassRepositoryInterface $crossBorderClassRepository,
        $mainTable = 'avatax_cross_border_class',
        $resourceModel = \ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass::class
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

        /**
         * @var CountryLinkCollection $countryLinkCollection
         */
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
}
