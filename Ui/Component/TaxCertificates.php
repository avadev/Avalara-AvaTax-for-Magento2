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

namespace ClassyLlama\AvaTax\Ui\Component;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;

/**
 * Class ExportButton
 */
class TaxCertificates extends AbstractComponent
{
    /**
     * Component name
     */
    const NAME = 'taxCertificates';

    /**
     * @var \ClassyLlama\AvaTax\Model\ResourceModel\Config
     */
    protected $configResourceModel;

    /**
     * ValidateAddress constructor
     *
     * @param ContextInterface                               $context
     * @param \ClassyLlama\AvaTax\Model\ResourceModel\Config $configResourceModel
     * @param array                                          $components
     * @param array                                          $data
     */
    public function __construct(
        ContextInterface $context,
        \ClassyLlama\AvaTax\Model\ResourceModel\Config $configResourceModel,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $components, $data);

        $this->configResourceModel = $configResourceModel;
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
     * @return void
     */
    public function prepare()
    {
        $gridComponent = $this->getComponent('customer_tax_certificates_grid');
        $config = $gridComponent->getData('config');
        $config['shouldShowWarning'] = $this->shouldShowWarning();

        $gridComponent->setData('config', $config);

        parent::prepare();
    }
}
