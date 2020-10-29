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

use ClassyLlama\AvaTax\Api\Data\CrossBorderClassInterface;

class CrossBorderClass extends \Magento\Framework\Model\AbstractModel implements CrossBorderClassInterface
{
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(\ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass::class);
    }

    /**
     * @inheritdoc
     */
    public function getDestinationCountries()
    {
        return $this->_getData(CrossBorderClassInterface::DESTINATION_COUNTRIES);
    }

    /**
     * @inheritdoc
     */
    public function setDestinationCountries($countries)
    {
        return $this->setData(CrossBorderClassInterface::DESTINATION_COUNTRIES, $countries);
    }

    /**
     * @inheritdoc
     */
    public function getCrossBorderTypeId()
    {
        return $this->_getData(CrossBorderClassInterface::CROSS_BORDER_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setCrossBorderTypeId($id)
    {
        return $this->setData(CrossBorderClassInterface::CROSS_BORDER_TYPE, $id);
    }

    /**
     * @inheritdoc
     */
    public function getHsCode()
    {
        return $this->_getData(CrossBorderClassInterface::HS_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setHsCode($code)
    {
        return $this->setData(CrossBorderClassInterface::HS_CODE, $code);
    }

    /**
     * @inheritdoc
     */
    public function getUnitName()
    {
        return $this->_getData(CrossBorderClassInterface::UNIT_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setUnitName($name)
    {
        return $this->setData(CrossBorderClassInterface::UNIT_NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getUnitAmountAttrCode()
    {
        return $this->_getData(CrossBorderClassInterface::UNIT_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setUnitAmountAttrCode($attrCode)
    {
        return $this->setData(CrossBorderClassInterface::UNIT_AMOUNT, $attrCode);
    }

    /**
     * @inheritdoc
     */
    public function getPrefProgramIndicator()
    {
        return $this->_getData(CrossBorderClassInterface::PREF_PROGRAM_IND);
    }

    /**
     * @inheritdoc
     */
    public function setPrefProgramIndicator($indicator)
    {
        return $this->setData(CrossBorderClassInterface::PREF_PROGRAM_IND, $indicator);
    }
}