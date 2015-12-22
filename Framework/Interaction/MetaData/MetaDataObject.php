<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\MetaData;

use Magento\Framework\Phrase;

class MetaDataObject
{
    const ALL_NAME = '*';

    /**
     * @var MetaDataObjectFactory
     */
    protected $metaDataObjectFactory = null;

    /**
     * @var ArrayTypeFactory
     */
    protected $arrayTypeFactory = null;

    /**
     * @var BooleanTypeFactory
     */
    protected $booleanTypeFactory = null;

    /**
     * @var DoubleTypeFactory
     */
    protected $doubleTypeFactory = null;

    /**
     * @var IntegerTypeFactory
     */
    protected $integerTypeFactory = null;

    /**
     * @var ObjectTypeFactory
     */
    protected $objectTypeFactory = null;

    /**
     * @var StringTypeFactory
     */
    protected $stringTypeFactory = null;

    /**
     * Stores all of the validation fields
     *
     * @var array
     */
    protected $metaDataProperties = [];

    /**
     * Stores all required fields
     *
     * @var MetaDataAbstract[]
     */
    protected $requiredRules = [];

    /**
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param ArrayTypeFactory $arrayTypeFactory
     * @param BooleanTypeFactory $booleanTypeFactory
     * @param DoubleTypeFactory $doubleTypeFactory
     * @param IntegerTypeFactory $integerTypeFactory
     * @param ObjectTypeFactory $objectTypeFactory
     * @param StringTypeFactory $stringTypeFactory
     * @param array $metaDataProperties
     */
    public function __construct(
        MetaDataObjectFactory $metaDataObjectFactory,
        ArrayTypeFactory $arrayTypeFactory,
        BooleanTypeFactory $booleanTypeFactory,
        DoubleTypeFactory $doubleTypeFactory,
        IntegerTypeFactory $integerTypeFactory,
        ObjectTypeFactory $objectTypeFactory,
        StringTypeFactory $stringTypeFactory,
        array $metaDataProperties
    ) {
        $this->metaDataObjectFactory = $metaDataObjectFactory;
        $this->arrayTypeFactory = $arrayTypeFactory;
        $this->booleanTypeFactory = $booleanTypeFactory;
        $this->doubleTypeFactory = $doubleTypeFactory;
        $this->integerTypeFactory = $integerTypeFactory;
        $this->objectTypeFactory = $objectTypeFactory;
        $this->stringTypeFactory = $stringTypeFactory;
        foreach ($metaDataProperties as $name => $metaDataRule) {
            if (in_array($metaDataRule[MetaDataAbstract::ATTR_TYPE], MetaDataAbstract::$types)) {
                $subtype = isset($metaDataRule[MetaDataAbstract::ATTR_SUBTYPE]) ?
                    $metaDataRule[MetaDataAbstract::ATTR_SUBTYPE] :
                    null;
                if (isset($subtype) && !($subtype instanceof $this)) {
                    $metaDataRule[MetaDataAbstract::ATTR_SUBTYPE] = $this->metaDataObjectFactory->create(
                        ['metaDataProperties' => $subtype]
                    );
                }

                $factoryVariableName = $metaDataRule[MetaDataAbstract::ATTR_TYPE] . 'TypeFactory';

                /** @var $rule MetaDataAbstract */
                $rule = $this->$factoryVariableName->create(
                    ['name' => $name, 'data' => $metaDataRule]
                );
                $this->metaDataProperties[$rule->getName()] = $rule;

                if ($rule->getRequired()) {
                    $this->requiredRules[$rule->getName()] = $rule;
                }
            }
        }
    }

    /**
     * Validates an array of values according to the initializing rules
     * TODO: Test all this to make sure it actually works as expected
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data
     * @return array
     * @throws ValidationException
     */
    public function validateData(array $data)
    {
        $validatedData = [];

        /** @var $defaultValidator MetaDataAbstract */
        $defaultValidator = isset($this->metaDataProperties[self::ALL_NAME]) ?
            $this->metaDataProperties[self::ALL_NAME] :
            null;

        foreach ($data as $name => $item) {
            /** @var $validator MetaDataAbstract */
            $validator = isset($this->metaDataProperties[$name]) ? $this->metaDataProperties[$name] : $defaultValidator;
            if (!is_null($validator)) {
                $validatedData[$name] = $validator->validateData($item);
            }
        }

        foreach ($this->requiredRules as $requiredRule) {
            if (!array_key_exists($requiredRule->getName(), $validatedData)) {
                throw new ValidationException(new Phrase(
                    '%1 is a required field and was either not passed in or did not pass validation.',
                    [
                        $requiredRule->getName()
                    ]
                ));
            }
        }
        return $validatedData;
    }
}