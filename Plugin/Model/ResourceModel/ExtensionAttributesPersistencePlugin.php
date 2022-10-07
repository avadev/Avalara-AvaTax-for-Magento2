<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Plugin\Model\ResourceModel;

use ClassyLlama\AvaTax\Helper\Config;
use ClassyLlama\AvaTax\Helper\ExtensionAttributeMerger;
use Magento\Framework\Api\ExtensionAttribute\Config\Converter;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ExtensionAttributesPersistencePlugin
{
    /**
     * @var ExtensionAttributesFactory
     */
    protected $extensionAttributesFactory;

    /**
     * @var ExtensionAttributeMerger
     */
    protected $extensionAttributeMerger;

    /**
     * @var bool
     */
    protected $shouldLoad;

    /**
     * @var bool
     */
    protected $shouldSave;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     * @param ExtensionAttributeMerger $extensionAttributeMerger
     * @param Config $config
     * @param bool $shouldLoad
     * @param bool $shouldSave
     */
    public function __construct(
        ExtensionAttributesFactory $extensionAttributesFactory,
        ExtensionAttributeMerger $extensionAttributeMerger,
        Config $config,
        $shouldLoad = true,
        $shouldSave = true
    ) {
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->extensionAttributeMerger = $extensionAttributeMerger;
        $this->shouldLoad = $shouldLoad;
        $this->shouldSave = $shouldSave;
        $this->config = $config;
    }

    /**
     * Gets join directives from extension attributes
     *
     * Copied from @param $extensibleEntityClass
     *
     * @return array
     * @see \Magento\Framework\Api\ExtensionAttribute\JoinProcessor::getJoinDirectivesForType
     *
     */
    protected function getJoinDirectivesForType($extensibleEntityClass)
    {
        $joinDirectives = [];

        foreach (
            $this->extensionAttributeMerger->getExtensibleConfig(
                $extensibleEntityClass
            ) as $attributeCode => $attributeConfig
        ) {
            if (isset($attributeConfig[Converter::JOIN_DIRECTIVE])) {
                $joinDirectives[$attributeCode] = $attributeConfig[Converter::JOIN_DIRECTIVE];
                $joinDirectives[$attributeCode][Converter::DATA_TYPE] = $attributeConfig[Converter::DATA_TYPE];
            }
        }

        $extensibleInterfaceName = $this->extensionAttributesFactory->getExtensibleInterfaceName($extensibleEntityClass);
        $stringArray = explode('\\', $extensibleInterfaceName);
        $extensibleEntityName = strtolower(str_replace('Interface', '', end($stringArray)));

        return array_filter(
            $joinDirectives,
            function ($extensionAttributeCode) use ($extensibleEntityName) {
                return in_array(
                    $extensionAttributeCode,
                    $this->config->getConfigDataArray(
                        "tax/avatax_advanced/attribute_codes/$extensibleEntityName")
                );
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @param AbstractDb $subject
     * @param callable $proceed
     * @param AbstractModel $object
     *
     * @return mixed
     */
    public function aroundSave(AbstractDb $subject, callable $proceed, AbstractModel $object)
    {
        $proceed($object);

        if (!$this->shouldSave) {
            return $subject;
        }

        $tablesToUpdate = [];
        $tableData = [];
        $tableFields = [];

        $extensionAttributes = $object->getData('extension_attributes');

        $joinDirectives = $this->getJoinDirectivesForType(get_class($object));

        // Compile join data with extension attribute data for building SQL queries
        foreach ($joinDirectives as $attributeCode => $directive) {
            $attributeData = null;

            if ($extensionAttributes !== null) {
                $attributeData = $this->extensionAttributeMerger->getExtensionAttribute(
                    $extensionAttributes,
                    $attributeCode
                );
            }

            $tablesToUpdate[$directive['join_reference_table']] = $directive;
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

        // Update each table reference with extension attribute data
        foreach (array_keys($tablesToUpdate) as $tableName) {

            // The "if" have been added for excluding conflict with extension Magento_NegotiableQuote(Magento Commerce 2.3.*)
            // It will be removed after implementing the compatibility between ClassyLlama_AvaTax and Magento_B2b
            $tableNameExemptions = $this->config->getTableExemptions();
            if(in_array($tableName, $tableNameExemptions)) { continue; }

            $data = array_merge(...$tableData[$tableName]);
            $joinReferenceField = $tablesToUpdate[$tableName]['join_reference_field'];
            // If a user switches their destination address from one that has cross border data to one that doesn't
            $allValuesAreNull = \count(
                    array_filter(
                        array_values(
                            array_diff_assoc(
                                $data,
                                [$joinReferenceField => $data[$joinReferenceField]]
                            )
                        )
                    )
                ) === 0;

            if ($object->isDeleted() || $extensionAttributes === null || $allValuesAreNull) {
                $deleteId = $object->getData(
                    $tablesToUpdate[$tableName]['join_on_field']
                );

                if ($deleteId) {
                    $subject->getConnection()->delete(
                        $subject->getTable($tableName),
                        "{$joinReferenceField} = {$deleteId}"
                    );
                }

                continue;
            }

            $subject->getConnection()->insertOnDuplicate(
                $subject->getTable($tableName),
                array_merge(...$tableData[$tableName]),
                array_merge(...$tableFields[$tableName])
            );
        }

        return $subject;
    }

    /**
     * Grabs data from extension attribute join tables and sets them on the object's extension attributes
     *
     * @param AbstractDb $subject
     * @param callable $proceed
     * @param AbstractModel $object
     * @param               $value
     * @param null $field
     *
     * @return AbstractDb
     */
    public function aroundLoad(AbstractDb $subject, callable $proceed, AbstractModel $object, $value, $field = null)
    {
        /** @var DataObject $object */
        $proceed($object, $value, $field);

        if (!$this->shouldLoad || $object instanceof \Magento\Sales\Api\Data\OrderInterface) {
            return $subject;
        }

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
                    'join_reference_field'       => $directive['join_reference_field'],
                    'join_reference_field_value' => $object->getData($directive['join_on_field']),
                    'attribute_codes'            => []
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


        foreach (array_unique(array_keys($tablesToUpdate)) as $tableName) {
            $fields = array_merge(...$tableFields[$tableName]);
            $select = $subject->getConnection()->select()->from($subject->getTable($tableName))->columns($fields)->where(
                $tablesToUpdate[$tableName]['join_reference_field'],
                $tablesToUpdate[$tableName]['join_reference_field_value']
            );

            $data = $subject->getConnection()->fetchRow($select);
            if ($data) {
                foreach ($tablesToUpdate[$tableName]['attribute_codes'] as $attributeCode => $fields) {
                    foreach ($fields as $field) {
                        $this->extensionAttributeMerger->setExtensionAttribute(
                            $extensionAttributes,
                            $attributeCode,
                            $data[$field]
                        );
                    }
                }
            }
        }

        $object->setData('extension_attributes', $extensionAttributes);

        return $subject;
    }
}
