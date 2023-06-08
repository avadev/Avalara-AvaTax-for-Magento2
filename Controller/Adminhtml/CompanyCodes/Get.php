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

namespace ClassyLlama\AvaTax\Controller\Adminhtml\CompanyCodes;

use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use Magento\Framework\DataObject;
use ClassyLlama\AvaTax\Helper\ApiLog;

/**
 * @codeCoverageIgnore
 */
class Get extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultPageFactory;

    /**
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company
     */
    protected $company;

    /**
     * @var \ClassyLlama\AvaTax\Helper\Config
     */
    private $config;

    /**
     * @var ApiLog
     */
    protected $apiLog;

    /**
     * @param \Magento\Backend\App\Action\Context                    $context
     * @param \Magento\Framework\Controller\Result\JsonFactory       $resultPageFactory
     * @param \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company $company
     * @param \ClassyLlama\AvaTax\Helper\Config                      $config
     * @param ApiLog                                                 $apiLog
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultPageFactory,
        \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company $company,
        \ClassyLlama\AvaTax\Helper\Config $config,
        ApiLog $apiLog
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->company = $company;
        $this->config = $config;
        $this->apiLog = $apiLog;
    }

    /**
     * {@inheritDoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Tax::config_tax');
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $companies = [];
        /** @var \Magento\Framework\HTTP\PhpEnvironment\Request $request */
        $request = $this->getRequest();
        $postValue = $request->getPostValue();
        $isProduction = (bool)$request->getParam('mode');
        $resultJson = $this->resultPageFactory->create();
        $scope = isset($postValue['scope']) ? $postValue['scope'] : null;
        $scopeType = $postValue['scope_type'] === 'global' ? \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            : $postValue['scope_type'];
        $currentCompanyId = $this->config->getCompanyId($scope, $scopeType, $isProduction);
        $message = "Avatax API connection successful.";

        try {
            if (!isset($postValue['license_key'])) {
                $postValue['license_key'] = $this->config->getLicenseKey($scope, $scopeType, $isProduction);
            }

            $companies = $this->company->getCompaniesWithSecurity(
                $postValue['account_number'],
                $postValue['license_key'],
                null,
                $isProduction
            );
            if (\count($companies) === 0)
                $message = "Avatax API connection successful but no company created under current avatax account";
        } catch (AvataxConnectionException $e) {
            $message = "Avatax API connection un-successful.";
        } catch (\Exception $e) {
            $message = "Avatax API connection un-successful.";
        }
        if (isset($postValue['license_key']))
            $this->apiLog->testConnectionLog($message, $scope, $scopeType);

        if (\count($companies) === 0) {
            return $resultJson->setData(
                [
                    'companies' => [],
                    'current_id' => $currentCompanyId
                ]
            );
        }

        return $resultJson->setData(
            [
                'companies' => array_map(
                    function ($company) {
                        /** @var DataObject $company */
                        return [
                            'company_id' => $company->getData('id'),
                            'company_code' => $company->getData('company_code'),
                            'name' => $company->getData('name'),
                        ];
                    },
                    $companies
                ),
                'current_id' => $currentCompanyId
            ]
        );
    }
}