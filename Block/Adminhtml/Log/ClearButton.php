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

namespace ClassyLlama\AvaTax\Block\Adminhtml\Log;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

/**
 * Class ClearButton
 */
/**
 * @codeCoverageIgnore
 */
class ClearButton implements ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(Context $context)
    {
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $message = __(
            'This will clear any logs that are older than the lifetime set in configuration. ' .
            'Do you want to continue?'
        );
        return [
            'label' => __('Clear Logs Now'),
            'on_click' => "confirmSetLocation('{$message}', '{$this->getButtonUrl()}')"
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    protected function getButtonUrl()
    {
        return $this->urlBuilder->getUrl('*/*/clear');
    }
}
