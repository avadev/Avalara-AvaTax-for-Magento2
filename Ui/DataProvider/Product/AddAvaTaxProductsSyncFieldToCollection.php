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
use ClassyLlama\AvaTax\Helper\Config;
class AddAvaTaxProductsSyncFieldToCollection implements \Magento\Ui\DataProvider\AddFieldToCollectionInterface 
{ 
    /**
     * @var Config
     */
    protected $config;
    /**
     * @param Config $config
     */
    public function __construct( Config $config )
    {
        $this->config = $config;
    }

    public function addField(\Magento\Framework\Data\Collection $collection, $field, $alias = null) 
    { 
        $companyId = $this->config->getCompanyId();
        if($companyId && !empty($companyId))
            $collection->joinField('syncstatus', 'avatax_products_sync', 'syncstatus', 'itemcode=sku', 'companyid='.$companyId, 'left'); 
    } 
}