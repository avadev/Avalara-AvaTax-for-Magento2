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
use ClassyLlama\AvaTax\Helper\Config as MainConfig;
use Magento\Store\Model\ScopeInterface;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass as CrossBorderClassResource;

/**
 * AvaTax Config model
 */
class CustomsConfig extends AbstractHelper
{
    const XML_PATH_AVATAX_CUSTOMS_ENABLED = 'tax/avatax_customs/enabled';

    const PRODUCT_ATTR_CROSS_BORDER_TYPE = 'avatax_cross_border_type';

    /**
     * @var Config
     */
    protected $mainConfig;

    /**
     * @var CrossBorderClassResource
     */
    protected $crossBorderClassResource;

    /**
     * @param Context $context
     * @param MainConfig $mainConfig
     * @param CrossBorderClassResource $crossBorderClassResource
     */
    public function __construct(
        Context $context,
        MainConfig $mainConfig,
        CrossBorderClassResource $crossBorderClassResource
    ) {
        $this->mainConfig = $mainConfig;
        $this->crossBorderClassResource = $crossBorderClassResource;
        parent::__construct($context);
    }

    /**
     * Are Customs features enabled?
     *
     * @param null|string $store
     * @param string $scopeType
     *
     * @return bool
     */
    public function enabled($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return (bool) $this->mainConfig->isModuleEnabled()
            && (bool) $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_CUSTOMS_ENABLED,
            $scopeType,
            $store
        );
    }

    /**
     * Get list of product attribute codes that are used for unit amount
     *
     * return array     Array of attribute codes
     */
    public function getUnitAmountAttributes()
    {
        return $this->crossBorderClassResource->getUnitAmountAttributes();
    }
}