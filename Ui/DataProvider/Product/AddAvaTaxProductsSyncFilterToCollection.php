<?php 
/*
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace ClassyLlama\AvaTax\Ui\DataProvider\Product; 
class AddAvaTaxProductsSyncFilterToCollection implements \Magento\Ui\DataProvider\AddFilterToCollectionInterface 
{ 
    public function addFilter(\Magento\Framework\Data\Collection $collection, $field, $condition = null) 
    { 
        if (isset($condition['eq'])) { 
            $collection->addFieldToFilter($field, $condition); 
        } 
    } 
}