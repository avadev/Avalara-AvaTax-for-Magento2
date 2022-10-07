<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Framework\Interaction\Request;

use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\RequestInterface;
use Magento\Framework\DataObject;

/**
 * Class Request
 * @package ClassyLlama\AvaTax\Framework\Interaction\Request
 */
class Request extends DataObject implements RequestInterface
{
    /**
     * The purpose is to have the same checksum of the Request object, which will be used as a key of the cache
     * @see \ClassyLlama\AvaTax\Framework\Interaction\Request\TaxComposite::calculateTax
     *
     * @return void
     */
    public function __clone()
    {
        /**
         * We have to clone properly "line" objects within an array
         * @see \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax::getTax
         * @see \ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax::setLineDetails
         */
        foreach (get_object_vars($this) as $name => $value) {
            if (is_array($this->$name)) {
                foreach ($this->$name as &$arrayValue) {
                    if (is_array($arrayValue)) {
                        // loops through objects within the array and clone them
                        foreach ($arrayValue as &$item) {
                            if (is_object($item)) {
                                $item = clone $item;
                            }
                            unset($item);
                        }
                    }
                }
            }
        }
    }
}
