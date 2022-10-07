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

namespace ClassyLlama\AvaTax\Ui\Component\Form\Element;

use ClassyLlama\AvaTax\Helper\Config;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\AbstractComponent;
use ClassyLlama\AvaTax\Api\UiComponentV1Interface;

/**
 * Class ValidateAddress
 * @package ClassyLlama\AvaTax\Ui\Component\Form\Element
 */
/**
 * @codeCoverageIgnore
 */
class ValidateAddress extends AbstractComponent implements UiComponentV1Interface
{
    /**
     * Component name
     */
    const NAME = 'validateButton';

    /**
     * Validate address path
     */
    const VALIDATE_ADDRESS_PATH = 'avatax/address/validation';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ValidateAddress constructor
     *
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
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
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($config['options'])) {
            $options = [];
            foreach ($config['options'] as $option) {
                $option['url'] = $this->urlBuilder->getUrl($option['url']);
                $options[] = $option;
            }
            $config['options'] = $options;
        }

        $store = $this->storeManager->getStore();
        $config['validationEnabled'] = $this->config->isAddressValidationEnabled($store);
        $hasChoice = $this->config->allowUserToChooseAddress($store);
        if ($hasChoice) {
            $instructions = $this->config->getAddressValidationInstructionsWithChoice($store);
        } else {
            $instructions = $this->config->getAddressValidationInstructionsWithOutChoice($store);
        }
        $config['instructions'] =  $instructions;
        $config['errorInstructions'] =  $this->config->getAddressValidationErrorInstructions($store);
        $config['countriesEnabled'] = $this->config->getAddressValidationCountriesEnabled($store);
        $config['baseUrl'] = $this->urlBuilder->getUrl(self::VALIDATE_ADDRESS_PATH);

        $this->setData('config', $config);

        parent::prepare();
    }
}
