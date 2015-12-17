<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\MetaData;

use Magento\Framework\Phrase;

class ValidationObject
{
    const ALL_NAME = '*';

    /**
     * @var ValidationObjectFactory
     */
    protected $validationObjectFactory = null;

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
    protected $validationRules = [];

    /**
     * Stores all required fields
     *
     * @var MetaDataAbstract[]
     */
    protected $requiredRules = [];

    /**
     * @param ValidationObjectFactory $validationObjectFactory
     * @param ArrayTypeFactory $arrayTypeFactory
     * @param BooleanTypeFactory $booleanTypeFactory
     * @param DoubleTypeFactory $doubleTypeFactory
     * @param IntegerTypeFactory $integerTypeFactory
     * @param ObjectTypeFactory $objectTypeFactory
     * @param StringTypeFactory $stringTypeFactory
     * @param array $validationRules
     */
    public function __construct(
        ValidationObjectFactory $validationObjectFactory,
        ArrayTypeFactory $arrayTypeFactory,
        BooleanTypeFactory $booleanTypeFactory,
        DoubleTypeFactory $doubleTypeFactory,
        IntegerTypeFactory $integerTypeFactory,
        ObjectTypeFactory $objectTypeFactory,
        StringTypeFactory $stringTypeFactory,
        array $validationRules
    ) {
        $this->validationObjectFactory = $validationObjectFactory;
        $this->arrayTypeFactory = $arrayTypeFactory;
        $this->booleanTypeFactory = $booleanTypeFactory;
        $this->doubleTypeFactory = $doubleTypeFactory;
        $this->integerTypeFactory = $integerTypeFactory;
        $this->objectTypeFactory = $objectTypeFactory;
        $this->stringTypeFactory = $stringTypeFactory;
        foreach ($validationRules as $name => $validationRule) {
            if (in_array($validationRule[MetaDataAbstract::ATTR_TYPE], MetaDataAbstract::$types)) {
                if (isset($validationRule[MetaDataAbstract::ATTR_SUBTYPE])) {
                    $validationRule[MetaDataAbstract::ATTR_SUBTYPE] = $this->validationObjectFactory->create(
                        ['validationRules' => $validationRule[MetaDataAbstract::ATTR_SUBTYPE]]
                    );
                }

                $factoryVariableName = $validationRule[MetaDataAbstract::ATTR_TYPE] . 'TypeFactory';

                /** @var $rule MetaDataAbstract */
                $rule = $this->$factoryVariableName->create(
                    ['name' => $name, 'data' => $validationRule]
                );
                $this->validationRules[$rule->getName()] = $rule;

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
        $defaultValidator = isset($this->validationRules[self::ALL_NAME]) ?
            $this->validationRules[self::ALL_NAME] :
            null;

        foreach ($data as $name => $item) {
            /** @var $validator MetaDataAbstract */
            $validator = isset($this->validationRules[$name]) ? $this->validationRules[$name] : $defaultValidator;
            if (!is_null($validator)) {
                $validatedData[$name] = $validator->validateData($item);
            }
        }

        foreach ($this->requiredRules as $requiredRule) {
            if (!isset($validatedData[$requiredRule->getName()])) {
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