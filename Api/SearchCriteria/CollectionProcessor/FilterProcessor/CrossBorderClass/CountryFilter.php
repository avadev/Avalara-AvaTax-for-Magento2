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

namespace ClassyLlama\AvaTax\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CrossBorderClass;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * @codeCoverageIgnore
 */
class CountryFilter implements CustomFilterInterface
{
    /**
     * Apply country filter to Cross Border Class collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool
     *
     * @throws \Magento\Framework\Exception\InputException
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        $countries = $filter->getValue();
        if (!is_array($countries)) {
            throw new \Magento\Framework\Exception\InputException(__('Countries filter must be an array'));
        }

        /** @var \ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\Collection $collection */
        $collection->filterByCountries($countries);

        return true;
    }
}
