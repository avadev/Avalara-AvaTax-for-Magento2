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

namespace ClassyLlama\AvaTax\Plugin\Model;

use Magento\Config\Model\Config;
use ClassyLlama\AvaTax\Helper\Config as AvataxConfig;
use ClassyLlama\AvaTax\Model\TaxCodeSync;

/**
 * @codeCoverageIgnore
 */
class ConfigPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

     /**
     * @var Config
     */
    protected $avataxConfig = null;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     *
     * @var TaxCodeSync
     */
    protected $taxCodeSync;

    /**
     * AroundSaveConfig constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param TaxCodeSync $taxCodeSync
     * @param AvataxConfig $avataxConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        TaxCodeSync $taxCodeSync,
        AvataxConfig $avataxConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->avataxConfig = $avataxConfig;
        $this->taxCodeSync = $taxCodeSync;
    }

    public function around__call(Config $subject, $proceed, $methodName, $args)
    {
        $result = $proceed($methodName, $args);

        if ($methodName !== 'getGroups') {
            return $result;
        }

        if (isset($result['avatax_general']['fields']['development_company_id']['inherit'])) {
            $result['avatax']['fields']['development_company_code']['inherit'] = $result['avatax_general']['fields']['development_company_id']['inherit'];
        }

        if (isset($result['avatax_general']['fields']['production_company_id']['inherit'])) {
            $result['avatax']['fields']['production_company_code']['inherit'] = $result['avatax_general']['fields']['production_company_id']['inherit'];
        }

        return $result;
    }

    /**
     * Avatax product tax code synch handling after config value was saved.
     *
     * @param Config $subject
     * @param callable $proceed
     * @return $this
     */
    public function aroundSave(
        Config $subject,
        callable $proceed
    ) {
        $section = $subject->getSection();

        if ($subject->getStore()) {
            $scopeId = $subject->getStore();
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        } else if ($subject->getWebsite()) {
            $scopeId = $subject->getWebsite();
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        } else {
            $scopeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }

        $oldConfigs = $this->scopeConfig->getValue($section, $scopeType, $scopeId);

        //Proceed call
        $returnValue = $proceed();

        $newConfigs = $this->scopeConfig->getValue($section, $scopeType, $scopeId);

        // Check for comapny id value whether its changed or not
        $oldConfigsValues = array_key_exists("avatax", $oldConfigs) ? $oldConfigs['avatax'] : [];
        $newConfigsValues = array_key_exists("avatax", $newConfigs) ? $newConfigs['avatax'] : [];

        $changedCompany = $this->checkDifference($oldConfigsValues, $newConfigsValues);

        // Condition to check if company value is getting changed then only syncing avatax product tax codes
        if($changedCompany) {            
            $companyId = $this->avataxConfig->getCompanyId($scopeId, $scopeType);
            $message = '';

            // Check status of module whether module is enabled or not
            if (!$this->avataxConfig->isModuleEnabled($scopeId, $scopeType)) {
                return $returnValue;
            }

            $isProduction = $this->avataxConfig->isProductionMode($scopeId, $scopeType);
            
            // Check that credentials have been set for whichever mode has been chosen
            if ($this->checkCredentialsForMode($scopeId, $scopeType, $isProduction)) {
                try {
                    // Sync product tax codes from Avatax
                    $resultObj[] = $this->taxCodeSync->synchTaxCodes($companyId, $isProduction, $scopeId, $scopeType);

                    if ($resultObj && array_sum($resultObj) > 0) {
                        $message = __(
                            '#%1 tax codes successfully synced from AvaTax.',
                            array_sum($resultObj)
                        );
                    } else {
                        $message = __(
                            'All tax codes already synced from AvaTax.'
                        );
                    }
                    $this->messageManager->addSuccessMessage($message);

                } catch (\Exception $exception) {
                    $message = $exception->getMessage();
                    $error = __(
                        'Error encountered in tax code sync, %1',
                        $message
                    );
                    $this->messageManager->addErrorMessage($error);
                }
            }
        }

        return $returnValue;
    }

    /**
     * Check for company id value 
     *
     * @param array $oldConfigs
     * @param array $newConfigs
     * 
     * @return bool
     */
    public function checkDifference($oldConfigs, $newConfigs)
    {
        $result = array_diff($oldConfigs,$newConfigs);
        if (array_key_exists("development_company_id",$result) || array_key_exists("production_company_id",$result))
        {
            $difference = true;
        } else {
            $difference = false;
        }
        return $difference;
    }

    /**
     * Check that credentials have been set for the supplied mode
     *
     * @param $scopeId
     * @param $scopeType
     * @param $isProduction
     *
     * @return bool
     */
    protected function checkCredentialsForMode($scopeId, $scopeType, $isProduction)
    {
        // Check that credentials have been set for whichever mode has been chosen
        if ($this->avataxConfig->getAccountNumber($scopeId, $scopeType, $isProduction) !== '' && $this->avataxConfig->getLicenseKey(
                $scopeId,
                $scopeType,
                $isProduction
            ) !== '' && $this->avataxConfig->getCompanyCode($scopeId, $scopeType, $isProduction) !== '') {
            return true;
        }

        // When one or more of the supplied mode's credentials is blank
        $this->messageManager->addWarningMessage(
            __(
                'The AvaTax extension is set to "%1" mode, but %2 credentials are incomplete.',
                $isProduction,
                strtolower($isProduction)
            )
        );

        return false;
    }
}
