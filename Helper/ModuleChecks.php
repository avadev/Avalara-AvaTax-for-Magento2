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
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Tax\Api\TaxRuleRepositoryInterface;

/**
 * Class ModuleChecks
 */
class ModuleChecks extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Store manager object
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var TaxRuleRepositoryInterface
     */
    protected $taxRuleRepository;

    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * ModuleChecks constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param TaxRuleRepositoryInterface $taxRuleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $avaTaxConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        TaxRuleRepositoryInterface $taxRuleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $avaTaxConfig,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->storeManager = $storeManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->backendUrl = $backendUrl;
        return parent::__construct($context);
    }

    /**
     * Get module check errors
     *
     * @return array
     */
    public function getModuleCheckErrors()
    {
        $errors = array();
        $errors = array_merge(
            $errors,
            $this->checkSslSupport(),
            $this->checkOriginAddress(),
            $this->checkNativeTaxRules()
        );

        return $errors;
    }

    /**
     * Ensure that the Origin Address has been configured
     *
     * @return array
     */
    public function checkOriginAddress()
    {
        $errors = [];

        if ($this->avaTaxConfig->isModuleEnabled()
            && $this->avaTaxConfig->getTaxMode($this->storeManager->getDefaultStoreView())
                != Config::TAX_MODE_NO_ESTIMATE_OR_SUBMIT
            && (
                !$this->scopeConfig->getValue(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID)
                || !$this->scopeConfig->getValue(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_REGION_ID)
                || !$this->scopeConfig->getValue(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_CITY)
                || !$this->scopeConfig->getValue(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_POSTCODE)
            )
        ) {
            $errors[] = __('In order for AvaTax tax calculation to work, you need to configure the <strong>Origin '
                . 'Address</strong> on the <a href="%1">Shipping Settings page</a>.',
                $this->backendUrl->getUrl('admin/system_config/edit', ['section' => 'shipping'])
            );
        }

        return $errors;
    }

    /**
     * Check to see if there are any native tax rules created that may affect AvaTax
     *
     * @return array
     */
    public function checkNativeTaxRules()
    {
        $errors = [];
        if ($this->avaTaxConfig->isModuleEnabled()
            && $this->avaTaxConfig->getTaxMode($this->storeManager->getDefaultStoreView())
                != Config::TAX_MODE_NO_ESTIMATE_OR_SUBMIT
            && !$this->avaTaxConfig->isNativeTaxRulesIgnored()
        ) {
            $taxRules = $this->taxRuleRepository->getList($this->searchCriteriaBuilder->create());
            if (count($taxRules->getItems())) {
                $errors[] = __(
                    'You have %1 native Magento Tax Rule(s) configured. '
                        . 'Please <a href="%2">review the tax rule(s)</a> and delete any that you do not specifically want enabled. '
                        . 'You should only have rules setup if you want to use them as backup rules in case of AvaTax '
                        . 'errors (see <a href="#row_tax_avatax_error_handling_header">Error Action setting</a>) '
                        . 'or if you need to support VAT tax. '
                        . '<a href="%3">Ignore this notification</a>.',
                    count($taxRules->getItems()),
                    $this->backendUrl->getUrl('tax/rule'),
                    $this->backendUrl->getUrl('avatax/tax/ignoreTaxRuleNotification')
                );
            }
        }
        return $errors;
    }

    /**
     * Check SSL support
     *
     * @return array
     */
    protected function checkSslSupport()
    {
        $errors = [];
        if (!function_exists('openssl_sign')) {
            $errors[] = __(
                'SSL must be enabled in PHP to use this extension. Typically, OpenSSL is used but it is not enabled on your server. This may not be a problem if you have some other form of SSL in place. For more information about OpenSSL, see %1.',
                '<a href="http://www.php.net/manual/en/book.openssl.php" target="_blank">http://www.php.net/manual/en/book.openssl.php</a>'
            );
        }

        return $errors;
    }
}
