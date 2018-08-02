<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Plugin\Model\ResourceModel;

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

    protected function getExtensionAttributeMethodName($key)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
    }

    protected function getExtensionAttribute($extensionAttributes, $key)
    {
        $methodCall = 'get' . $this->getExtensionAttributeMethodName($key);

        return $extensionAttributes->{$methodCall}();
    }

    protected function setExtensionAttribute($extensionAttributes, $key, $value)
    {
        $methodCall = 'set' . $this->getExtensionAttributeMethodName($key);

        return $extensionAttributes->{$methodCall}($value);
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
            $attributeData = $this->getExtensionAttribute($extensionAttributes, $attributeCode);

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

        $joinDirectives = $this->getJoinDirectivesForType(get_class($object));
        $tablesToUpdate = [];
        $tableFields = [];

        $extensionAttributes = $object->getData('extension_attributes');

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionAttributesFactory->create(get_class($object), []);
        }

        foreach ($joinDirectives as $attributeCode => $directive) {
            if (!isset($tablesToUpdate[$directive['join_reference_table']])) {
                $tablesToUpdate[$directive['join_reference_table']] = [
                    'join_reference_field' => $directive['join_reference_field'],
                    'join_reference_field_value' => $object->getData($directive['join_on_field']),
                    'attribute_codes' => []
                ];
            }

            $fields = [];

            foreach ($directive['fields'] as $fieldDirective) {
                $fields[] = $fieldDirective['field'];
            }

            if (!isset($tableFields[$directive['join_reference_table']])) {
                $tableFields[$directive['join_reference_table']] = [];
            }

            $tableFields[$directive['join_reference_table']][] = $fields;
            $tablesToUpdate[$directive['join_reference_table']]['attribute_codes'][$attributeCode] = $fields;
        }

        foreach (array_unique($tablesToUpdate) as $tableName => $tableDirective) {
            $fields = array_merge(...$tableFields[$tableName]);
            $select = $subject->getConnection()->select()->from($tableName)->columns($fields)->where(
                $tablesToUpdate[$tableName]['join_reference_field'],
                $tablesToUpdate[$tableName]['join_reference_field_value']
            );

            $data = $subject->getConnection()->fetchRow($select);

            foreach ($tablesToUpdate[$tableName]['attribute_codes'] as $attributeCode => $fields) {
                foreach ($fields as $field) {
                    $this->setExtensionAttribute($extensionAttributes, $attributeCode, $data[$field]);
                }
            }
        }

        $object->setData('extension_attributes', $extensionAttributes);

        return $subject;
    }
}