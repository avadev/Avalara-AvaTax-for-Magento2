<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Plugin\Tax\Model;

use ClassyLlama\AvaTax\Helper\Config as ConfigHelper;

class Config
{
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * Config constructor.
     * @param ConfigHelper $configHelper
     */
    public function __construct(ConfigHelper $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * @param \Magento\Tax\Model\Config $subject
     * @param $result
     * @param null $store
     * @return bool
     */
    public function afterDiscountTax (\Magento\Tax\Model\Config $subject, $result, $store = null)
    {
        if ($this->configHelper->isModuleEnabled($store)) {
            // AvaTax extension is enabled, whether or not the discount includes tax should correspond to whether or not
            // the catalog prices include tax
            return $subject->priceIncludesTax($store);
        }
        return $result;
    }
}
