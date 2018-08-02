<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Plugin\Model\ResourceModel\Quote;

use Magento\Framework\Api\ExtensionAttribute\Config\Converter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorHelper;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ExtensionAttributesPersistencePlugin
{
    /**
     * @var JoinProcessorHelper
     */
    protected $joinProcessorHelper;

    /**
     * @var ExtensionAttributesFactory
     */
    protected $extensionAttributesFactory;

    /**
     * @param JoinProcessorHelper        $joinProcessorHelper
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(
        JoinProcessorHelper $joinProcessorHelper,
        ExtensionAttributesFactory $extensionAttributesFactory
    )
    {
        $this->joinProcessorHelper = $joinProcessorHelper;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    protected function getJoinDirectivesForType($extensibleEntityClass)
    {
        $extensibleInterfaceName = $this->extensionAttributesFactory->getExtensibleInterfaceName(
            $extensibleEntityClass
        );
        $extensibleInterfaceName = ltrim($extensibleInterfaceName, '\\');
        $config = $this->joinProcessorHelper->getConfigData();

        if (!isset($config[$extensibleInterfaceName])) {
            return [];
        }

        $typeAttributesConfig = $config[$extensibleInterfaceName];
        $joinDirectives = [];

        foreach ($typeAttributesConfig as $attributeCode => $attributeConfig) {
            if (isset($attributeConfig[Converter::JOIN_DIRECTIVE])) {
                $joinDirectives[$attributeCode] = $attributeConfig[Converter::JOIN_DIRECTIVE];
                $joinDirectives[$attributeCode][Converter::DATA_TYPE] = $attributeConfig[Converter::DATA_TYPE];
            }
        }

        return $joinDirectives;
    }

    protected function getDataFromExtensionAttributes($extensionAttributes, $key)
    {
        $methodCall = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));

        return $extensionAttributes->{$methodCall}();
    }

    /**
     * @param AbstractDb    $subject
     * @param callable      $proceed
     * @param AbstractModel $object
     *
     * @return mixed
     */
    public function aroundSave(AbstractDb $subject, callable $proceed, AbstractModel $object)
    {
        $proceed($object);

        $extensionAttributes = $object->getData('extension_attributes');

        if ($extensionAttributes === null) {
            return $subject;
        }

        $joinDirectives = $this->getJoinDirectivesForType(get_class($object));

        $tablesToUpdate = [];
        $tableData = [];
        $tableFields = [];

        foreach ($joinDirectives as $attributeCode => $directive) {
            $attributeData = $this->getDataFromExtensionAttributes($extensionAttributes, $attributeCode);

            $tablesToUpdate[] = $directive['join_reference_table'];
            $dataToSave = [$directive['join_reference_field'] => $object->getId()];
            $fields = [];

            foreach ($directive['fields'] as $fieldDirective) {
                $fields[] = $fieldDirective['field'];
                $dataToSave[$fieldDirective['field']] = $attributeData;
            }

            if (!isset($tableData[$directive['join_reference_table']])) {
                $tableData[$directive['join_reference_table']] = [];
            }

            if (!isset($tableFields[$directive['join_reference_table']])) {
                $tableFields[$directive['join_reference_table']] = [];
            }

            $tableData[$directive['join_reference_table']][] = $dataToSave;
            $tableFields[$directive['join_reference_table']][] = $fields;
        }

        foreach (array_unique($tablesToUpdate) as $tableName) {
            $subject->getConnection()->insertOnDuplicate(
                $tableName,
                array_merge(...$tableData[$tableName]),
                array_merge(...$tableFields[$tableName])
            );
        }

        return $subject;
    }

    public function aroundLoad(AbstractDb $subject, callable $proceed, AbstractModel $object, $value, $field = null)
    {
        /** @var DataObject $object */
        $proceed($object, $value, $field);

        return $subject;
    }
}