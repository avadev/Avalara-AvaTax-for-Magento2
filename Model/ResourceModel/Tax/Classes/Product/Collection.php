<?php
namespace ClassyLlama\AvaTax\Model\ResourceModel\Tax\Classes\Product;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Class Collection
 */
class Collection extends SearchResult
{
    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFieldToFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT);
        return $this;
    }
}
