<?php

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Api\SerializerInterface;
use \InvalidArgumentException;

/**
 * Class Serialize
 * @package ClassyLlama\AvaTax\Model
 */
class Serialize implements SerializerInterface
{
    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|resource|null $data
     * @return string|bool
     * @throws InvalidArgumentException
     */
    public function serialize($data)
    {
        if (is_resource($data)) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        return serialize($data);
    }

    /**
     * Unserialize the given string
     *
     * @param string|null $string
     * @param array $params
     * @return string|int|float|bool|array|null
     * @throws InvalidArgumentException
     */
    public function unserialize(?string $string = '', array $params = ['allowed_classes' => false])
    {
        if (null === $string || '' === $string) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }
        set_error_handler(
            function () {
                restore_error_handler();
                throw new \InvalidArgumentException('Unable to unserialize value, string is corrupted.');
            },
            E_NOTICE
        );
        $result = unserialize($string, $params);
        restore_error_handler();
        return $result;
    }
}