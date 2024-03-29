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

declare(strict_types=1);

namespace ClassyLlama\AvaTax\Block\ViewModel;

use Magento\Store\Model\ScopeInterface;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Rest\Company as CompanyRest;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use ClassyLlama\AvaTax\Helper\DocumentManagementConfig;
use Magento\Store\Model\StoreManagerInterface;

class AccountAddExemptionZone implements ArgumentInterface
{
    /**#@+
     * XML paths to configuration.
     */
    public const XML_PATH_CERTCAPTURE_AUTO_VALIDATION = 'tax/avatax_certificate_capture/disable_certcapture_auto_validation';
    /**#@-*/

    /**
     * @var CompanyRest
     */
    protected $companyRest;

    private $scopeConfig;
    /**
     * @var AvaTaxLogger
     */
    private $avaTaxLogger;

    /**
     * @var DocumentManagementConfig
     */
    protected $documentManagementConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param CompanyRest $companyRest
     */
    public function __construct(
        CompanyRest $companyRest,
        ScopeConfigInterface $scopeConfig,
        AvaTaxLogger $avaTaxLogger,
        DocumentManagementConfig $documentManagementConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->companyRest = $companyRest;
        $this->scopeConfig = $scopeConfig;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->documentManagementConfig = $documentManagementConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return false|string
     */
    public function getCertificateExposureZonesJsConfig()
    {
        try {
            $zones = $this->companyRest->getCertificateExposureZones();
            // code to get enabled countries
            $enabledCountries = $this->documentManagementConfig->getEnabledCountries($this->storeManager->getStore()->getId());
            $zonesRes = array();
            if (!empty($enabledCountries)) {
                foreach ($enabledCountries as $val) {
                    $zonesRes = array_merge($zonesRes, array_values(array_filter(array_map(function ($zone) use ($val){
                    return $zone->country == $val ? $zone->name : "";
                    }, $zones->value))));
                }
            }
            return json_encode($zonesRes);
        } catch (\Throwable $exception) {
            $this->avaTaxLogger->error($exception->getMessage(), [
               'class' => self::class,
               'trace' => $exception->getTraceAsString(),
            ]);
        }

        return '';
    }

    /**
     * @return string
     */
    public function isCertificatesAutoValidationDisabled(): string
    {
        return (string)(int) $this->scopeConfig->isSetFlag(
            self::XML_PATH_CERTCAPTURE_AUTO_VALIDATION,
            ScopeInterface::SCOPE_STORE
        );
    }
}
