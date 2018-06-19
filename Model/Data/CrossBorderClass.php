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

namespace ClassyLlama\AvaTax\Model\Data;

class CrossBorderClass extends \Magento\Framework\Api\AbstractSimpleObject implements
    \ClassyLlama\AvaTax\Api\Data\CrossBorderClassInterface
{
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getDestinationCountries()
    {
        return $this->_get(self::DESTINATION_COUNTRIES);
    }

    /**
     * @inheritdoc
     */
    public function setDestinationCountries($countries)
    {
        return $this->setData(self::DESTINATION_COUNTRIES, $countries);
    }

    /**
     * @inheritdoc
     */
    public function getCrossBorderType()
    {
        return $this->_get(self::CROSS_BORDER_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setCrossBorderType($type)
    {
        return $this->setData(self::CROSS_BORDER_TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getHsCode()
    {
        return $this->_get(self::HS_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setHsCode($code)
    {
        return $this->setData(self::HS_CODE, $code);
    }

    /**
     * @inheritdoc
     */
    public function getUnitName()
    {
        return $this->_get(self::UNIT_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setUnitName($name)
    {
        return $this->setData(self::UNIT_NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getUnitAmountAttrCode()
    {
        return $this->_get(self::UNIT_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setUnitAmountAttrCode($attrCode)
    {
        return $this->setData(self::UNIT_AMOUNT, $attrCode);
    }

    /**
     * @inheritdoc
     */
    public function getPrefProgramIndicator()
    {
        return $this->_get(self::PREF_PROGRAM_IND);
    }

    /**
     * @inheritdoc
     */
    public function setPrefProgramIndicator($indicator)
    {
        return $this->setData(self::PREF_PROGRAM_IND, $indicator);
    }
}