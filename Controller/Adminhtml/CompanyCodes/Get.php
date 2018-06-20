<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 * @author      sean.templeton
 */

namespace ClassyLlama\AvaTax\Controller\Adminhtml\CompanyCodes;

use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use Magento\Framework\DataObject;

class Get extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    /**
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company
     */
    protected $company;

    /**
     * @var \ClassyLlama\AvaTax\Helper\Config
     */
    private $config;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultPageFactory,
        \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company $company,
        \ClassyLlama\AvaTax\Helper\Config $config
    )
    {
        parent::__construct( $context );
        $this->resultPageFactory = $resultPageFactory;
        $this->company = $company;
        $this->config = $config;
    }

    public function execute()
    {
        $postValue = $this->getRequest()->getPostValue();
        $mode = $this->getRequest()->getParam( 'mode' );
        $resultJson = $this->resultPageFactory->create();
        $companies = [];
        $currentCompanyCode = null;
        $scope = isset( $postValue['scope'] ) ? $postValue['scope'] : null;
        $scopeType = $postValue['scope_type'] === 'global' ? \Magento\Store\Model\ScopeInterface::SCOPE_STORE : $postValue['scope_type'];

        switch ((int) $mode)
        {
            case \ClassyLlama\AvaTax\Model\Config\Source\Mode::DEVELOPMENT:
                $currentCompanyCode = $this->config->getDevelopmentCompanyCode( $scope, $scopeType );
                break;
            case \ClassyLlama\AvaTax\Model\Config\Source\Mode::PRODUCTION:
                $currentCompanyCode = $this->config->getCompanyCode( $scope, $scopeType );
                break;
        }

        try
        {
            if (!isset( $postValue['license_key'] ))
            {
                switch ((int) $mode)
                {
                    case \ClassyLlama\AvaTax\Model\Config\Source\Mode::DEVELOPMENT:
                        $postValue['license_key'] = $this->config->getDevelopmentLicenseKey( $scope, $scopeType );
                        break;
                    case \ClassyLlama\AvaTax\Model\Config\Source\Mode::PRODUCTION:
                        $postValue['license_key'] = $this->config->getLicenseKey( $scope, $scopeType );
                        break;
                }
            }

            $companies = $this->company->getCompaniesWithSecurity( $postValue['account_number'], $postValue['license_key'], null, $mode );
        }
        catch (AvataxConnectionException $e)
        {
        }

        if (\count( $companies ) === 0)
        {
            return $resultJson->setData(
                [
                    'companies'    => [
                        [
                            'account_id'   => null,
                            'company_code' => null,
                            'name'         => __( 'No available companies' ),
                        ]
                    ],
                    'current_code' => $currentCompanyCode
                ]
            );
        }

        return $resultJson->setData(
            [
                'companies'    => array_map(
                    function ( $company ) {
                        /** @var DataObject $company */
                        return [
                            'account_id'   => $company->getData( 'account_id' ),
                            'company_code' => $company->getData( 'company_code' ),
                            'name'         => $company->getData( 'name' ),
                        ];
                    },
                    $companies
                ),
                'current_code' => $currentCompanyCode
            ]
        );
    }
}