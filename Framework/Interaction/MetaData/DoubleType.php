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
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Framework\Interaction\MetaData;

use Magento\Framework\Phrase;

class DoubleType extends MetaDataAbstract
{
    /**
     * @param string $name
     * @param array $data
     */
    public function __construct($name, array $data = [])
    {
        parent::__construct('double', $name, $data);
    }

    /**
     * Pass in a value and get the validated value back
     *
     * @param mixed $value
     * @return mixed
     * @throws ValidationException
     */
    public function validateData($value)
    {
        $value = $this->validateSimpleType($value);
        $value = $this->validateOptions($value);

        return $value;
    }
}
