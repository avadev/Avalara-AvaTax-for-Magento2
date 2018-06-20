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
use ClassyLlama\AvaTax\Api\Data\CrossBorderClassInterfaceFactory;

class CrossBorderClass extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var CrossBorderClassInterfaceFactory
     */
    protected $crossBorderClassFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CrossBorderClassInterfaceFactory $crossBorderClassFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        CrossBorderClassInterfaceFactory $crossBorderClassFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->crossBorderClassFactory = $crossBorderClassFactory;
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
     * Retrieve data model with cross-border class data
     *
     * @return CrossBorderClassInterface
     */
    public function getDataModel()
    {
        $dataObject = $this->createDataModel();

        $dataObject->setId($this->getClassId());
        $dataObject->setDestinationCountries($this->getDestinationCountryCodes());
        $dataObject->setCrossBorderType($this->getCrossBorderType());
        $dataObject->setHsCode($this->getHsCode());
        $dataObject->setUnitName($this->getUnitName());
        $dataObject->setUnitAmountAttrCode($this->getUnitAmountProductAttr());
        $dataObject->setPrefProgramIndicator($this->getPrefProgramIndicator());

        return $dataObject;
    }

    /**
     * Create a new instance of a data model
     *
     * @return CrossBorderClassInterface
     */
    public function createDataModel()
    {
        /**
         * @var CrossBorderClassInterface $dataObject
         */
        $dataObject = $this->crossBorderClassFactory->create();
        return $dataObject;
    }

    /**
     * Get array of the applicable destination country codes
     *
     * @return array
     */
    public function getDestinationCountryCodes()
    {
        // TODO: Replace with actual logic
        return [
            'US', 'DE'
        ];
    }
}