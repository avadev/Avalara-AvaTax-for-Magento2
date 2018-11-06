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

namespace ClassyLlama\AvaTax\Ui\Component;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\Registry;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Class ExportButton
 */
class TaxCertificates extends AbstractComponent
{
    /**
     * Component name
     */
    const NAME = 'taxCertificates';

    const COMPONENT = 'ClassyLlama_AvaTax/js/form/certificates-fieldset';

    /**
     * @var \ClassyLlama\AvaTax\Model\ResourceModel\Config
     */
    protected $configResourceModel;

    /**
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company
     */
    protected $companyRest;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $backendUrl;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    protected $sessionContext;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * ValidateAddress constructor
     *
     * @param ContextInterface                                       $context
     * @param \ClassyLlama\AvaTax\Model\ResourceModel\Config         $configResourceModel
     * @param \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company $companyRest
     * @param \Magento\Backend\Model\Url                             $backendUrl
     * @param \Magento\Backend\Block\Template\Context                $sessionContext
     * @param Registry                                               $registry
     * @param array                                                  $components
     * @param array                                                  $data
     */
    public function __construct(
        ContextInterface $context,
        \ClassyLlama\AvaTax\Model\ResourceModel\Config $configResourceModel,
        \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company $companyRest,
        \Magento\Backend\Model\Url $backendUrl,
        \Magento\Backend\Block\Template\Context $sessionContext,
        Registry $registry,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $components, $data);

        $this->configResourceModel = $configResourceModel;
        $this->companyRest = $companyRest;
        $this->backendUrl = $backendUrl;
        $this->sessionContext = $sessionContext;
        $this->registry = $registry;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @return bool
     */
    public function shouldShowWarning()
    {
        try {
            return $this->configResourceModel->getConfigCount(
                    [
                        \ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_DEVELOPMENT_COMPANY_CODE,
                        \ClassyLlama\AvaTax\Helper\Config::XML_PATH_AVATAX_PRODUCTION_COMPANY_CODE
                    ]
                ) > 2;
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * @return array
     * @throws \ClassyLlama\AvaTax\Exception\AvataxConnectionException
     */
    protected function getAvailableExemptionZones() {
        $zones = $this->companyRest->getCertificateExposureZones();

        return array_map(function($zone) {return $zone->name;}, $zones->value);
    }

    /**
     * @return void
     * @throws \ClassyLlama\AvaTax\Exception\AvataxConnectionException
     */
    public function prepare()
    {
        $gridComponent = $this->getComponent('customer_tax_certificates_grid');
        $config = $gridComponent->getData('config');
        $config['shouldShowWarning'] = $this->shouldShowWarning();

        $gridComponent->setData('config', $config);

        // Configure our custome component instead of the fieldset
        $jsConfig = $this->getData('js_config');
        $jsConfig['component'] = static::COMPONENT;
        $this->setData('js_config', $jsConfig);

        // Configure the component for adding certificates
        $config = $this->getData('config');
        $config['customer_id'] = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $config['token_url'] = $this->backendUrl->getUrl(
            'avatax/certificatestoken/get',
            ['form_key' => $this->sessionContext->getFormKey()]
        );;
        $config['available_exemption_zones'] = $this->getAvailableExemptionZones();
        $this->setData('config', $config);

        parent::prepare();
    }
}
