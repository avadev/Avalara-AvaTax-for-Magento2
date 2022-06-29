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

use ClassyLlama\AvaTax\Helper\Config as MainConfig;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass as CrossBorderClassResource;

/**
 * AvaTax Config model
 */
class CustomsConfig extends AbstractHelper
{
    /**#@+
     * Customs config paths
     */
    const XML_PATH_AVATAX_CUSTOMS_ENABLED = 'tax/avatax_customs/enabled';

    const PRODUCT_ATTR_CROSS_BORDER_TYPE = 'avatax_cross_border_type';

    const XML_PATH_AVATAX_CUSTOMS_GROUND_SHIPPING_METHODS = 'tax/avatax_customs/ground_shipping_methods';

    const XML_PATH_AVATAX_CUSTOMS_OCEAN_SHIPPING_METHODS = 'tax/avatax_customs/ocean_shipping_methods';

    const XML_PATH_AVATAX_CUSTOMS_AIR_SHIPPING_METHODS = 'tax/avatax_customs/air_shipping_methods';

    const XML_PATH_AVATAX_CUSTOMS_CUSTOM_SHIPPING_METHODS_MAP = 'tax/avatax_customs/custom_shipping_methods_map';

    const XML_PATH_AVATAX_CUSTOMS_DEFAULT_SHIPPING_MODE = 'tax/avatax_customs/default_shipping_mode';

    const XML_PATH_AVATAX_DEFAULT_BORDER_TYPE = 'tax/avatax_customs/default_border_type';
    /**#@-*/

    /**#@+
     * Importer of Record Override Values
     */
    const CUSTOMER_IMPORTER_OF_RECORD_ATTRIBUTE = 'override_importer_of_record';

    const CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_DEFAULT = 'default';

    const CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_YES = "override_yes";

    const CUSTOMER_IMPORTER_OF_RECORD_OVERRIDE_NO = "override_no";
    /**#@-*/

    /**
     * Defines the strings that come from AvaTax that represent customs. Needed for compatibility for the current
     * and upcoming versions of the AvaTax API
     *
     * @var array
     */
    const CUSTOMS_NAMES = ['Customs', 'LandedCost'];

    /**
     * @var Config
     */
    protected $mainConfig;

    /**
     * @var CrossBorderClassResource
     */
    protected $crossBorderClassResource;

    /**
     * @var array
     */
    protected $shippingMappings;

    /**
     * @param Context    $context
     * @param MainConfig $mainConfig
     * @param CrossBorderClassResource $crossBorderClassResource
     */
    public function __construct(
        Context $context,
        MainConfig $mainConfig,
        CrossBorderClassResource $crossBorderClassResource
    )
    {
        $this->mainConfig = $mainConfig;
        $this->crossBorderClassResource = $crossBorderClassResource;
        parent::__construct($context);
    }

    /**
     * Are Customs features enabled?
     *
     * @param null|string $store
     * @param string      $scopeType
     *
     * @return bool
     */
    public function enabled($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return (bool)$this->mainConfig->isModuleEnabled() && (bool)$this->scopeConfig->getValue(
                self::XML_PATH_AVATAX_CUSTOMS_ENABLED,
                $scopeType,
                $store
            );
    }

    /**
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return array
     */
    public function getGroundShippingMethods($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return explode(
            ',',
            $this->scopeConfig->getValue(
                self::XML_PATH_AVATAX_CUSTOMS_GROUND_SHIPPING_METHODS,
                $scopeType,
                $store
            ) ?? ''
        );
    }

    /**
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return array
     */
    public function getOceanShippingMethods($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return explode(
            ',',
            $this->scopeConfig->getValue(
                self::XML_PATH_AVATAX_CUSTOMS_OCEAN_SHIPPING_METHODS,
                $scopeType,
                $store
            ) ?? ''
        );
    }

    /**
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return array
     */
    public function getAirShippingMethods($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return explode(
            ',',
            $this->scopeConfig->getValue(
                self::XML_PATH_AVATAX_CUSTOMS_AIR_SHIPPING_METHODS,
                $scopeType,
                $store
            ) ?? ''
        );
    }

    /**
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return array
     */
    public function getCustomShippingMethodMappings($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return \ClassyLlama\AvaTax\Block\Adminhtml\Form\Field\CustomShippingMethods::parseSerializedValue(
            $this->scopeConfig->getValue(
                self::XML_PATH_AVATAX_CUSTOMS_CUSTOM_SHIPPING_METHODS_MAP,
                $scopeType,
                $store
            )
        );
    }

    /**
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return string
     */
    public function getDefaultShippingType($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_CUSTOMS_DEFAULT_SHIPPING_MODE,
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

    /**
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return string
     */
    public function getDefaultBorderType($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_DEFAULT_BORDER_TYPE,
            $scopeType,
            $store
        );
    }

    public function getShippingTypeForMethod($method, $scopeId = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        if ($this->shippingMappings === null) {
            $groundShippingMethods = $this->getGroundShippingMethods($scopeId, $scopeType);
            $oceanShippingMethods = $this->getOceanShippingMethods($scopeId, $scopeType);
            $airShippingMethods = $this->getAirShippingMethods($scopeId, $scopeType);
            $customShippingMethods = $this->getCustomShippingMethodMappings($scopeId, $scopeType);

            $this->shippingMappings = array_merge(
                array_combine($groundShippingMethods, array_fill(0, \count($groundShippingMethods), 'ground')),
                array_combine($oceanShippingMethods, array_fill(0, \count($oceanShippingMethods), 'ocean')),
                array_combine($airShippingMethods, array_fill(0, \count($airShippingMethods), 'air')),
                $customShippingMethods
            );
        }

        if (isset($this->shippingMappings[$method])) {
            return $this->shippingMappings[$method];
        }

        // Return default method
        return $this->getDefaultShippingType($scopeId, $scopeType);
    }
}
