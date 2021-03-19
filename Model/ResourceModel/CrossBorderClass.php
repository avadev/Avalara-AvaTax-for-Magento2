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

namespace ClassyLlama\AvaTax\Model\ResourceModel;

use ClassyLlama\AvaTax\Model\CrossBorderClass\CountryLink;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CountryLink\Collection as CountryLinkCollection;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CountryLink\CollectionFactory as CountryLinkCollectionFactory;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CountryLink as CountryLinkResource;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CountryLinkFactory as CountryLinkResourceFactory;
use ClassyLlama\AvaTax\Model\CrossBorderClass\CountryLinkFactory;

class CrossBorderClass extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var CountryLinkCollectionFactory
     */
    protected $countryLinkCollectionFactory;

    /**
     * @var CountryLinkResourceFactory
     */
    protected $countryLinkResourceFactory;

    /**
     * @var CountryLinkFactory
     */
    protected $countryLinkFactory;

    /**=
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param CountryLinkCollectionFactory $countryLinkCollectionFactory
     * @param CountryLinkResourceFactory $countryLinkResourceFactory
     * @param CountryLinkFactory $countryLinkFactory
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        CountryLinkCollectionFactory $countryLinkCollectionFactory,
        CountryLinkResourceFactory $countryLinkResourceFactory,
        CountryLinkFactory $countryLinkFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->countryLinkCollectionFactory = $countryLinkCollectionFactory;
        $this->countryLinkResourceFactory = $countryLinkResourceFactory;
        $this->countryLinkFactory = $countryLinkFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('avatax_cross_border_class', 'class_id');
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $countries = $object->getDestinationCountries();
        if (!is_null($countries)) {
            // Get existing country associations
            /**
             * @var CountryLinkCollection $countryLinkCollection
             */
            $countryLinkCollection = $this->countryLinkCollectionFactory->create();
            $countryLinkCollection->addFieldToFilter('class_id', $object->getId());

            /**
             * @var CountryLink $countryLink
             */
            $existingCountriesByCode = [];
            foreach ($countryLinkCollection as $countryLink) {
                $existingCountriesByCode[$countryLink->getCountryId()] = $countryLink;
            }

            $existingCountries = array_keys($existingCountriesByCode);

            $countriesToDelete = array_diff($existingCountries, $countries);
            $countriesToAdd = array_diff($countries, $existingCountries);

            if (!empty($countriesToDelete) || !empty($countriesToAdd)) {
                /**
                 * @var CountryLinkResource $countryLinkResource
                 */
                $countryLinkResource = $this->countryLinkResourceFactory->create();

                // Delete country associations
                if (!empty($countriesToDelete)) {
                    foreach ($countriesToDelete as $countryToDelete) {
                        if (isset($existingCountriesByCode[$countryToDelete])) {
                            $countryLinkResource->delete($existingCountriesByCode[$countryToDelete]);
                        }
                    }
                }

                // Add country associations
                if (!empty($countriesToAdd)) {
                    foreach ($countriesToAdd as $countryToAdd) {
                        $countryLink = $this->countryLinkFactory->create();
                        $countryLink->setData([
                            'class_id' => $object->getId(),
                            'country_id' => $countryToAdd,
                        ]);

                        $countryLinkResource->save($countryLink);
                    }
                }
            }
        }
    }

    /**
     * Get list of product attribute codes that are used for unit amount
     *
     * return array     Array of attribute codes
     */
    public function getUnitAmountAttributes()
    {
        $select = $this->getConnection()->select()
            ->from(
                $this->getTable('avatax_cross_border_class'),
                ['unit_amount_product_attr']
            )
            ->group('unit_amount_product_attr');

        $results = $this->getConnection()->fetchCol($select);

        return array_unique($results);
    }
}
