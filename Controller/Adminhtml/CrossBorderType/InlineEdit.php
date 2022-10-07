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

namespace ClassyLlama\AvaTax\Controller\Adminhtml\CrossBorderType;

use ClassyLlama\AvaTax\Api\CrossBorderTypeRepositoryInterface;
use ClassyLlama\AvaTax\Api\Data\CrossBorderTypeInterfaceFactory;

/**
 * @codeCoverageIgnore
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var CrossBorderTypeRepositoryInterface
     */
    protected $crossBorderTypeRepository;

    /**
     * @var CrossBorderTypeInterfaceFactory
     */
    protected $crossBorderTypeInterfaceFactory;

    /**
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param CrossBorderTypeRepositoryInterface               $crossBorderTypeRepository
     * @param CrossBorderTypeInterfaceFactory                  $crossBorderTypeInterfaceFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        CrossBorderTypeRepositoryInterface $crossBorderTypeRepository,
        CrossBorderTypeInterfaceFactory $crossBorderTypeInterfaceFactory
    )
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->crossBorderTypeRepository = $crossBorderTypeRepository;
        $this->crossBorderTypeInterfaceFactory = $crossBorderTypeInterfaceFactory;
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $modelid) {
                    try {
                        $model = $this->crossBorderTypeInterfaceFactory->create();
                        $model->setData($postItems[$modelid]);

                        $this->crossBorderTypeRepository->save($model);
                    } catch (\Exception $e) {
                        $messages[] = "[Cross Border Type ID: {$modelid}]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData(['messages' => $messages, 'error' => $error]);
    }
}
