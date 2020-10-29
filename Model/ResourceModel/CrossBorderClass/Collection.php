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

namespace ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var bool
     */
    protected $countriesJoined = false;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\ClassyLlama\AvaTax\Model\CrossBorderClass::class, \ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass::class);
    }

    /**
     * Filter classes by the countries associated with them
     *
     * @param int[] $countryIds
     * @return $this
     */
    public function filterByCountries($countryIds) {
        if (!empty($countryIds)) {
            $this->joinCountries();
            $this->getSelect()->where('country_links.country_id IN (?)', $countryIds);
        }

        return $this;
    }

    /**
     * Join country associations
     *
     * @return $this
     */
    protected function joinCountries()
    {
        if (!$this->countriesJoined) {
            $this->getSelect()->join(
                ['country_links' => $this->getTable('avatax_cross_border_class_country')],
                'main_table.class_id = country_links.class_id',
                []
            )->group('main_table.class_id');

            $this->countriesJoined = true;
        }

        return $this;
    }
}