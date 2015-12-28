<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ClassyLlama\AvaTax\Component;

use ClassyLlama\AvaTax\Model\Config;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\AbstractComponent;

/**
 * Class ExportButton
 */
class ValidateButton extends AbstractComponent
{
    /**
     * Component name
     */
    const NAME = 'validateButton';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
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

        $config['validation_enabled'] = $this->config->isAddressValidationEnabled();
        $hasChoice = $this->config->allowUserToChooseAddress();
        $config['has_choice'] = $hasChoice;
        if ($hasChoice) {
            $instructions = $this->config->getAddressValidationInstructionsWithChoice();
        } else {
            $instructions = $this->config->getAddressValidationInstructionsWithOutChoice();
        }
        $config['instructions'] =  $instructions;
        $config['error_instructions'] =  $this->config->getAddressValidationErrorInstructions();
        $config['countries_enabled'] = $this->config->getAddressValidationCountriesEnabled();

        $this->setData('config', $config);

        parent::prepare();
    }
}
