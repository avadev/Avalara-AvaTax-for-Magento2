<?php

namespace ClassyLlama\AvaTax\Model\Quote;

use Magento\Quote\Model\Quote\Config as QuoteConfig;
use ClassyLlama\AvaTax\Model\Config;

class Plugin
{
    /**
     * @var Config
     */
    protected $config = null;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }
    /**
     * Append ref1 and ref2 attributes to the list of attributes loaded on the quote_items collection
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param Config $config
     * @param $attributes
     * @return array
     */
    public function afterGetProductAttributes(QuoteConfig $config, $attributes)
    {
        if ($this->config->getRef1()) {
            $attributes[] = $this->config->getRef1();
        }
        if ($this->config->getRef1()) {
            $attributes[] = $this->config->getRef1();
        }
        return array_unique($attributes);
    }
}
