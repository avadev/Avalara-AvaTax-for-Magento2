<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ClassyLlama\AvaTax\Ui\Component\Form\Element;

use ClassyLlama\AvaTax\Model\Config;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\AbstractComponent;

/**
 * Class ExportButton
 */
class ValidateAddress extends AbstractComponent
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
     * ValidateAddress constructor
     *
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param Config $config
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        Config $config,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
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

        $config['validationEnabled'] = $this->config->isAddressValidationEnabled();
        $hasChoice = $this->config->allowUserToChooseAddress();
        $config['choice'] = $hasChoice;
        if ($hasChoice) {
            $instructions = $this->config->getAddressValidationInstructionsWithChoice();
        } else {
            $instructions = $this->config->getAddressValidationInstructionsWithOutChoice();
        }
        $config['instructions'] =  $instructions;
        $config['errorInstructions'] =  $this->config->getAddressValidationErrorInstructions();
        $config['countriesEnabled'] = $this->config->getAddressValidationCountriesEnabled();
        $config['baseUrl'] = $this->urlBuilder->getUrl(self::VALIDATE_ADDRESS_PATH);

        $this->setData('config', $config);

        parent::prepare();
    }
}
