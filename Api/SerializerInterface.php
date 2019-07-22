<?php

namespace ClassyLlama\AvaTax\Api;

use \InvalidArgumentException;

/**
 * Interface SerializerInterface
 * @package ClassyLlama\AvaTax\Api
 */
interface SerializerInterface
{

    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|resource|null $data
     * @return string|bool
     * @throws InvalidArgumentException
     */
    public function serialize($data);

    /**
     * Unserialize the given string
     *
     * @param string|null $string
     * @param array $params
     * @return string|int|float|bool|array|null
     * @throws InvalidArgumentException
     */
    public function unserialize(?string $string, array $params);
}
