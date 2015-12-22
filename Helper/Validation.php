<?php

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Validation
{
    /**
     * List of types that will be attempted to convert to
     *
     * @var array
     */
    protected $simpleTypes = ['boolean', 'integer', 'string', 'double'];

    /**
     * List of all available types
     *
     * @var array
     */
    protected $types = ['boolean', 'integer', 'string', 'double', 'object', 'array'];

    // All available attribute keys
    const ATTR_TYPE = 'type';
    const ATTR_FORMAT = 'format';
    const ATTR_REQUIRED = 'required';
    const ATTR_CLASS = 'class';
    const ATTR_LENGTH = 'length';
    const ATTR_OPTIONS = 'options';
    const ATTR_SUBTYPE = 'subtype';

    /**
     * Remove all non-valid fields from data, convert incorrectly typed data to the correctly typed data,
     * validate length, and validate existence
     *
     * Syntax for validFields array, curly brackets indicate optional keys, variable values are denoted by underscores
     *  [
     *      '_key_' => ['type' => 'integer'{, 'required' => _boolean_}],
     *      '_key_' => ['type' => 'boolean'{, 'required' => _boolean_}],
     *      '_key_' => ['type' => 'float'{, 'required' => _boolean_}],
     *      '_key_' => ['type' => 'object'{, 'required' => _boolean_}{, 'class' => '_\Class\Name_'}],
     *      '_key_' => [
     *          'type' => 'string'
     *          {, 'required' => _boolean_}
     *          {, 'length' => _length_}
     *          {, 'format' => '_regexFormat_'}
     *          {, 'options' => ['_option1_', '_option2_', '_option3_', ...]}
     *      ],
     *      '_key_' => [
     *          'type' => 'array'
     *          {, 'required' => _boolean_}
     *          {, 'length' => _length_}
     *          {, 'subtype' => [_validFieldsArrayDefinition_]}
     *      ],
     *  ]
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $data array
     * @param $validFields array
     * @return mixed
     * @throws LocalizedException
     */
    public function validateData(array $data, $validFields)
    {
        if (!$this->isValidFormat($validFields)) {
            throw new LocalizedException(
                new Phrase('The $validFields were not in a valid format as defined in the doc block.')
            );
        }

        $all = '*';

        // Save data for later information
        $origData = $data;

        // Check each element in the array to make sure it is valid
        foreach ($data as $key => $value) {
            $currentField = null;

            if (array_key_exists($key, $validFields)) {
                $currentField = $validFields[$key];
            }

            // If the current array key does not exist in the fields template but there is an all data type,
            // assign that as the template.
            if (!isset($currentField) && array_key_exists($all, $validFields)) {
                $currentField = $validFields[$all];
            }

            // If no type is set, unset the value
            if (!isset($currentField) || !isset($currentField['type'])) {
                unset($data[$key]);
                continue;
            } elseif ('array' == $currentField['type']) {

                // Do not try to convert array values to array since that may not produce expected results
                if (gettype($value) != $currentField['type']) {
                    unset($data[$key]);
                    continue;
                }

                // If a subtype is defined, call this function for that contents of the array
                if (isset($currentField['subtype'])) {
                    $data[$key] = $this->validateData($value, $currentField['subtype']);
                }

                // If the length exceeds the maximum allowed length, throw an exception
                if (isset($currentField['length']) && count($value) > $currentField['length']) {
                    throw new LocalizedException(new Phrase(
                        'You attempted to pass data to the AvaTax API with the key of %1,' . '
                         with a length of %2, the max allowed length is %3.',
                        [
                            $key,
                            count($value),
                            $currentField['length'],
                        ]
                    ));
                }
            } elseif ('object' == $currentField['type'] &&
                'object' == gettype($value) &&
                isset($currentField['class'])) {

                // For values that should be objects, make sure they are the correct type of object.
                if (!($value instanceof $currentField['class'])) {
                    unset($data[$key]);
                    continue;
                }
            } elseif (in_array($currentField['type'], $this->simpleTypes)) {

                if (gettype($value) != $currentField['type']) {
                    // For simple types, try to convert a value to the correct type.
                    try {
                        settype($data[$key], $currentField['type']);
                    } catch (\Exception $e) {
                        throw new LocalizedException(new Phrase('Could not convert "%1" to a "%2"', [
                            $key,
                            $currentField,
                        ]));
                    }
                }
            } else {
                unset($data[$key]);
                continue;
            }

            // Make sure the value is a valid option if options are set
            if (isset($currentField['options']) && !array_search($value, $currentField['options'])) {
                unset($data[$key]);
                continue;
            }

            // Truncate strings that are longer than the longest allowed to maximum allowed
            if ('string' == $currentField['type'] &&
                isset($currentField['length']) &&
                strlen($value) > $currentField['length']) {
                $data[$key] = substr($value, 0, $currentField['length']);
            }

            // Validate the format of strings if one is set
            if ('string' == $currentField['type'] &&
                isset($currentField['format']) &&
                !preg_match($currentField['format'], $value)) {
                throw new LocalizedException(new Phrase('AvaTax requires %1 field to match the regex: "%2"', [
                    $key,
                    $currentField['format'],
                ]));
            }
        }

        foreach ($validFields as $key => $currentField) {
            if (isset($currentField['required']) && $currentField['required'] && (!isset($data[$key]) || empty($data[$key]))) {
                throw new LocalizedException(new Phrase(
                    'The AvaTax API requires the "%1" field and it was not submitted as the valid type.  ' .
                    'You passed a value of type "%2" and it was supposed to be of type "%3"',
                    [
                        $key,
                        isset($origData[$key]) ? gettype($origData[$key]) : 'null',
                        $currentField['type'],
                    ]
                ));
            }
        }
        return $data;
    }

    /**
     * Validate validation array
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param $validFields
     * @return bool
     */
    protected function isValidFormat($validFields)
    {
        foreach ($validFields as $field) {
            if (!is_array($field) || !isset($field[self::ATTR_TYPE])) {
                return false;
            }
            foreach ($field as $key => $property) {
                switch ($key) {
                    case self::ATTR_TYPE:
                        if (!in_array($property, $this->types)) {
                            return false;
                        }
                        continue 2;
                    case self::ATTR_FORMAT:
                        if (!is_string($property) || preg_match($property, null) === false) {
                            return false;
                        }
                        continue 2;
                    case self::ATTR_REQUIRED:
                        if (!is_bool($property)) {
                            return false;
                        }
                        continue 2;
                    case self::ATTR_CLASS:
                        if (!is_string($property) || !class_exists($property)) {
                            return false;
                        }
                        continue 2;
                    case self::ATTR_LENGTH:
                        if (!is_integer($property) || $property <= 0) {
                            return false;
                        }
                        continue 2;
                    case self::ATTR_OPTIONS:
                        if (!is_array($property)) {
                            return false;
                        }
                        foreach ($property as $option) {
                            if (gettype($option) != $field[self::ATTR_TYPE]) {
                                return false;
                            }
                        }
                        continue 2;
                    case self::ATTR_SUBTYPE:
                        if (!is_array($property)) {
                            return false;
                        }
                        if (!$this->isValidFormat($property)) {
                            return false;
                        }
                        continue 2;
                }
            }
        }
        return true;
    }
}