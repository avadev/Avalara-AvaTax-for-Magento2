<?php

namespace ClassyLlama\AvaTax\Block\Adminhtml\Queue;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class ClearButton
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
    public function __construct(\Magento\Backend\Block\Widget\Context $context)
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
            'This will clear any completed queued transmissions that have already been sent to AvaTax. ' .
            'This will also clear any failed transmissions that are older then the lifetime set in configuration. ' .
            'Any failed transmissions will need to be manually entered into AvaTax. ' .
            'Do you want to continue?'
        );
        return [
            'label' => __('Clear Queue Now'),
            'on_click' => "confirmSetLocation('{$message}', '{$this->getButtonUrl()}')"
//            'on_click' => 'deleteConfirm(\'' . $message . '\', \'' . $this->getButtonUrl() . '\')',
//            'on_click' => 'location.reload();',
//            'on_click' => sprintf("location.href = '%s';", $this->getButtonUrl())
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    protected function getButtonUrl()
    {
        return $this->urlBuilder->getUrl('*/*/clear', []);
    }
}
