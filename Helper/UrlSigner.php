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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
    public function __construct(Config $config, Context $context)
    {
        parent::__construct($context);

        $this->config = $config;
    }

    public function signParameters(array $parameters, $storeId = null, $store = ScopeInterface::SCOPE_STORE)
    {
        ksort($parameters);

        return hash_hmac('sha256', http_build_query($parameters), $this->config->getLicenseKey($storeId, $store));
    }
}