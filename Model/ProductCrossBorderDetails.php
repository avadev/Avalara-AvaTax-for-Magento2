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

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Api\Data\ProductCrossBorderDetailsInterface;

class ProductCrossBorderDetails extends \Magento\Framework\DataObject implements ProductCrossBorderDetailsInterface
{
    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->getData('product_id');
    }

    /**
     * @inheritdoc
     */
    public function setProductId($id)
    {
        return $this->setData('product_id', $id);
    }

    /**
     * @inheritdoc
     */
    public function getDestinationCountry()
    {
        return $this->getData('destination_country');
    }

    /**
     * @inheritdoc
     */
    public function setDestinationCountry($country)
    {
        return $this->setData('destination_country', $country);
    }

    /**
     * @inheritdoc
     */
    public function getHsCode()
    {
        return $this->getData('hs_code');
    }

    /**
     * @inheritdoc
     */
    public function setHsCode($code)
    {
        return $this->setData('hs_code', $code);
    }

    /**
     * @inheritdoc
     */
    public function getUnitName()
    {
        return $this->getData('unit_name');
    }

    /**
     * @inheritdoc
     */
    public function setUnitName($unit)
    {
        return $this->setData('unit_name', $unit);
    }

    /**
     * @inheritdoc
     */
    public function getUnitAmountAttrCode()
    {
        return $this->getData('unit_amount_code');
    }

    /**
     * @inheritdoc
     */
    public function setUnitAmountAttrCode($code)
    {
        return $this->setData('unit_amount_code', $code);
    }

    /**
     * @inheritdoc
     */
    public function getPrefProgramIndicator()
    {
        return $this->getData('pref_program_indicator');
    }

    /**
     * @inheritdoc
     */
    public function setPrefProgramIndicator($indicator)
    {
        return $this->setData('pref_program_indicator', $indicator);
    }
}