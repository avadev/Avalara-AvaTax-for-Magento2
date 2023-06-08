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

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Form widget for viewing log
 */
/**
 * @codeCoverageIgnore
 */
class View extends Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \ClassyLlama\AvaTax\Model\Log
     */
    protected $currentLog;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Add back button
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->buttonList->add(
            'back',
            [
                'label' => __('Back'),
                'onclick' => "setLocation('" . $this->_urlBuilder->getUrl('avatax/log') . "')",
                'class' => 'back'
            ]
        );
    }

    /**
     * Get log model
     *
     * @return \ClassyLlama\AvaTax\Model\Log
     */
    public function getLog()
    {
        if (null === $this->currentLog) {
            $this->currentLog = $this->coreRegistry->registry('current_log');
        }
        return $this->currentLog;
    }
}
