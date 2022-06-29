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

namespace ClassyLlama\AvaTax\Plugin\Controller\Account;

class CreatePostPlugin
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(\Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * Checks if there is a redirect param from the referer to create an account, and intercepts the redirect to
     * checkout if there is
     *
     * @param \Magento\Customer\Controller\Account\CreatePost $subject
     * @param                                                 $result
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function afterExecute(\Magento\Customer\Controller\Account\CreatePost $subject, $result)
    {
        $queryParameters = [];
        parse_str(parse_url($subject->getRequest()->getServer('HTTP_REFERER'), PHP_URL_QUERY) ?? '', $queryParameters);

        // If we don't have our redirect directive, ignore this result
        if (!isset($queryParameters['redirect']) || $queryParameters['redirect'] !== 'checkout') {
            return $result;
        }

        // If we have errors, reset the referrer, and ignore this result
        if (\count($this->messageManager->getMessages(false)->getErrors()) > 0) {
            $result->setRefererUrl();
            return $result;
        }

        // Set the redirect back to checkout
        $result->setPath('checkout');
        return $result;
    }
}
