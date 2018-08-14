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

namespace ClassyLlama\AvaTax\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;

/**
 * @method string getCarrierLabel
 * @method string getCarrierCode
 */
class CarrierShippingMethodsProvider extends DataObject
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \ClassyLlama\AvaTax\Api\CarrierShippingMethodsInterface
     */
    protected $carrierShippingMethods;

    /**
     * @param RequestInterface                                        $request
     * @param ScopeConfigInterface                                    $scopeConfig
     * @param \ClassyLlama\AvaTax\Api\CarrierShippingMethodsInterface $carrierShippingMethods
     * @param array                                                   $data
     */
    public function __construct(
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        \ClassyLlama\AvaTax\Api\CarrierShippingMethodsInterface $carrierShippingMethods,
        array $data = []
    )
    {
        parent::__construct($data);

        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->carrierShippingMethods = $carrierShippingMethods;
    }

    /**
     * @return array
     */
    protected function getScopeInfo()
    {
        $requestParams = $this->request->getParams();

        if (isset($requestParams['store'])) {
            return [\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $requestParams['store']];
        }

        if (isset($requestParams['website'])) {
            return [\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $requestParams['website']];
        }

        return [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null];
    }

    /**
     * @param $configPath
     *
     * @return array
     */
    protected function getCarrierConfig($configPath)
    {
        list($scopeType, $scopeId) = $this->getScopeInfo();

        return explode(',', $this->scopeConfig->getValue($configPath, $scopeType, $scopeId));
    }

    /**
     * @param array $values
     *
     * @return array
     */
    public function getCarrierOptions()
    {
        $options = [];
        $carrierCode = $this->getCarrierCode();
        $values = $this->carrierShippingMethods->getConfiguredMethods();
        $configData = $this->carrierShippingMethods->getCarrierMethods();

        foreach ($values as $value) {
            if (!isset($configData[$value])) {
                continue;
            }

            $options[] = ['value' => "{$carrierCode}_{$value}", 'label' => __($configData[$value])];
        }

        if (\count($options) === 0) {
            return [];
        }

        return [
            'value' => $options,
            'label' => $this->getCarrierLabel()
        ];
    }
}
