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

use ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterface;

class CrossBorderType extends \Magento\Framework\Model\AbstractModel implements CrossBorderTypeInterface
{
    /**
     * @var string
     */
    protected $eventPrefix = 'classyllama_avatax_crossbordertype';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderType::class);
    }

    /**
     * Get entity_id
     * @return string
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param string $entityId
     * @return \ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get type
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Set type
     * @param string $type
     * @return \ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }
}
