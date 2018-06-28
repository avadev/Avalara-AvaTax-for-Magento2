<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class UrlSigner extends AbstractHelper
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config  $config
     * @param Context $context
     */
    public function __construct( Config $config, Context $context )
    {
        parent::__construct( $context );

        $this->config = $config;
    }

    public function signParameters( array $parameters, $storeId = null, $store = ScopeInterface::SCOPE_STORE )
    {
        ksort($parameters);

        return hash_hmac('sha256', http_build_query($parameters), $this->config->getLicenseKey($storeId, $store));
    }
}