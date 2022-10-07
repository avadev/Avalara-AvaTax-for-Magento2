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
use ReflectionException;
use ReflectionMethod;

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

    /**
     * @param $key
     *
     * @see \Magento\Framework\DataObject::setDataUsingMethod
     *
     * @return mixed
     */
    protected function getExtensionAttributeMethodName($key)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
    }

    /**
     * @param $extensionAttributes
     * @param $key
     *
     * @return mixed
     */
    public function getExtensionAttribute($extensionAttributes, $key)
    {
        $methodCall = 'get' . $this->getExtensionAttributeMethodName($key);

        return $extensionAttributes->{$methodCall}();
    }

    /**
     * @param $extensionAttributes
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function setExtensionAttribute($extensionAttributes, $key, $value)
    {
        $methodCall = 'set' . $this->getExtensionAttributeMethodName($key);
        if ($this->canSetExtensionAttribute($extensionAttributes, $methodCall, $value)) {
            $extensionAttributes->{$methodCall}($value);
        }

        return $extensionAttributes;
    }

    /**
     * @param $extensionAttributes
     * @param $method
     * @param $value
     *
     * @return bool
     */
    private function canSetExtensionAttribute($extensionAttributes, $method, $value)
    {
        if ($value !== null) {
            return true;
        }

        try {
            $reflection = new ReflectionMethod($extensionAttributes, $method);
            // @codeCoverageIgnoreStart
            $parameter = current($reflection->getParameters());

            $result = $parameter->allowsNull();
            // @codeCoverageIgnoreEnd
        } catch (ReflectionException $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param $extensibleEntityClass
     *
     * @return array
     */
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
        // @codeCoverageIgnoreStart
        return $config[$extensibleInterfaceName];
        // @codeCoverageIgnoreEnd
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

    /**
     * @param \Magento\Framework\Model\AbstractExtensibleModel $from
     * @param \Magento\Framework\Model\AbstractExtensibleModel $to
     * @param array                                            $allowedlist
     */
    public function copyAttributes(
        \Magento\Framework\Model\AbstractExtensibleModel $from,
        \Magento\Framework\Model\AbstractExtensibleModel $to,
        $allowedlist = []
    )
    {
        $fromExtensionAttributes = $from->getData(ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY);
        $toExtensionAttributes = $to->getData(ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY);

        // If there are no extension attributes, just skip this
        if ($fromExtensionAttributes === null) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        if ($toExtensionAttributes === null) {
            $toExtensionAttributes = $this->extensionAttributesFactory->create(get_class($to));
        }

        $hasAllowedList = \count($allowedlist) > 0;

        foreach ($this->getIntersectingExtensionAttributes(get_class($from), get_class($to)) as $extensionAttribute) {
            // @codeCoverageIgnoreStart
            if ($hasAllowedList && !\in_array($extensionAttribute, $allowedlist)) {
                continue;
            }

            $this->setExtensionAttribute(
                $toExtensionAttributes,
                $extensionAttribute,
                $this->getExtensionAttribute($fromExtensionAttributes, $extensionAttribute)
            );
            // @codeCoverageIgnoreEnd
        }

        $to->setData(ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY, $toExtensionAttributes);
    }
}
