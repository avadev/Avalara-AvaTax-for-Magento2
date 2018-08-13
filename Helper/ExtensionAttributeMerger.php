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

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorHelper;

class ExtensionAttributeMerger
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    protected $extensionAttributesFactory;

    /**
     * @var JoinProcessorHelper
     */
    protected $joinProcessorHelper;

    /**
     * @param JoinProcessorHelper                               $joinProcessorHelper
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(
        JoinProcessorHelper $joinProcessorHelper,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributesFactory
    )
    {
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->joinProcessorHelper = $joinProcessorHelper;
    }

    protected function getExtensionAttributeMethodName($key)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
    }

    public function getExtensionAttribute($extensionAttributes, $key)
    {
        $methodCall = 'get' . $this->getExtensionAttributeMethodName($key);

        return $extensionAttributes->{$methodCall}();
    }

    public function setExtensionAttribute($extensionAttributes, $key, $value)
    {
        $methodCall = 'set' . $this->getExtensionAttributeMethodName($key);

        return $extensionAttributes->{$methodCall}($value);
    }

    public function getExtensibleConfig($extensibleEntityClass)
    {
        $extensibleInterfaceName = $this->extensionAttributesFactory->getExtensibleInterfaceName(
            $extensibleEntityClass
        );
        $extensibleInterfaceName = ltrim($extensibleInterfaceName, '\\');
        $config = $this->joinProcessorHelper->getConfigData();

        if (!isset($config[$extensibleInterfaceName])) {
            return [];
        }

        return $config[$extensibleInterfaceName];
    }

    /**
     * @param string $leftClassName
     * @param string $rightClassName
     *
     * @return array
     */
    protected function getIntersectingExtensionAttributes($leftClassName, $rightClassName)
    {
        return array_intersect(
            array_keys($this->getExtensibleConfig($leftClassName)),
            array_keys($this->getExtensibleConfig($rightClassName))
        );
    }

    public function copyAttributes(
        \Magento\Framework\Model\AbstractExtensibleModel $from,
        \Magento\Framework\Model\AbstractExtensibleModel $to
    )
    {
        $fromExtensionAttributes = $from->getData(ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY);
        $toExtensionAttributes = $to->getData(ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY);

        // If there are no extension attributes, just skip this
        if ($fromExtensionAttributes === null) {
            return;
        }

        if ($toExtensionAttributes === null) {
            $toExtensionAttributes = $this->extensionAttributesFactory->create(get_class($to));
        }

        foreach ($this->getIntersectingExtensionAttributes(get_class($from), get_class($to)) as $extensionAttribute) {
            $this->setExtensionAttribute(
                $toExtensionAttributes,
                $extensionAttribute,
                $this->getExtensionAttribute($fromExtensionAttributes, $extensionAttribute)
            );
        }

        $to->setData(ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY, $toExtensionAttributes);
    }
}