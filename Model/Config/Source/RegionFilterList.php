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

namespace ClassyLlama\AvaTax\Model\Config\Source;

use ClassyLlama\AvaTax\Helper\Config;

class RegionFilterList extends \Magento\Directory\Model\Config\Source\Allregion
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var Config
     */
    protected $config;

    /**
     * RegionList constructor.
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Element\Context $context
     * @param Config $config
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Context $context,
        Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->request = $context->getRequest();
        $this->config = $config;
        return parent::__construct($countryCollectionFactory, $regionCollectionFactory);
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray($isMultiselect = false)
    {
        if (!$this->_options) {
            $selectedCountries = $this->getCountryList();
            $countriesArray = $this->_countryCollectionFactory->create()
                ->addFieldToFilter("country_id", ['in' => $selectedCountries])
                ->load()->toOptionArray(false);
            $this->_countries = [];
            foreach ($countriesArray as $a) {
                $this->_countries[$a['value']] = $a['label'];
            }

            $countryRegions = [];
            $regionsCollection = $this->_regionCollectionFactory->create()->load();
            foreach ($regionsCollection as $region) {
                // Only add countries that have been selected by user
                if (!isset($this->_countries[$region->getCountryId()])) {
                    continue;
                }
                $countryRegions[$region->getCountryId()][$region->getId()] = $region->getDefaultName();
            }
            uksort($countryRegions, [$this, 'sortRegionCountries']);

            $this->_options = [];
            foreach ($countryRegions as $countryId => $regions) {
                $regionOptions = [];
                foreach ($regions as $regionId => $regionName) {
                    $regionOptions[] = ['label' => $regionName, 'value' => $regionId];
                }
                $this->_options[$countryId] = ['label' => $this->_countries[$countryId], 'value' => $regionOptions];
            }

            foreach ($this->_countries as $countryId => $countryLabel) {
                if (!isset($this->_options[$countryId])) {
                    $this->_options[$countryId] = [
                        'label' => $countryLabel,
                        'value' => [['label' => 'All', 'value' => $countryId]]
                    ];
                }
            }
            uksort($this->_options, [$this, 'sortRegionCountries']);
        }
        $options = $this->_options;
        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => '']);
        }

        return $options;
    }

    /**
     * Get country list
     *
     * @return array
     */
    protected function getCountryList()
    {
        // It seems odd to check the parameters directly, but it's the same pattern being used in Magento_Backend
        if ($this->request->getParam('store')) {
            $scopeId = $this->request->getParam('store');
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        } elseif ($this->request->getParam('website')) {
            $scopeId = $this->request->getParam('website');
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        } elseif ($this->request->getParam('group')) {
            $scopeId = $this->request->getParam('website');
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        } else {
            $scopeId = $this->storeManager->getDefaultStoreView();
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }

        return explode(',', $this->config->getTaxCalculationCountriesEnabled($scopeId, $scopeType));
    }
}
