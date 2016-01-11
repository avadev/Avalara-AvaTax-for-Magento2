<?php

namespace ClassyLlama\AvaTax\Model\Message;

use ClassyLlama\AvaTax\Model\Config;
use Magento\Framework\Notification\MessageInterface;

/**
 * ConfigNotification class
 */
class ConfigNotification implements MessageInterface
{
    /**
     * Store manager object
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Config $config
     * @param \Magento\Tax\Model\Config $taxConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        Config $config,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->config = $config;
        $this->taxConfig = $taxConfig;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('AVATAX_CONFIG_NOTIFICATION');
    }

    /**
     * Check whether notification is displayed
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return $this->getText()
            && $this->isTaxConfigPage()
            && $this->config->isModuleEnabled();
    }

    /**
     * Return whether page is tax configuration
     *
     * @return bool
     */
    protected function isTaxConfigPage()
    {
        return $this->request->getModuleName() == 'admin'
            && $this->request->getControllerName() == 'system_config'
            && $this->request->getActionName() == 'edit'
            && $this->request->getParam('section') == 'tax';
    }

    /**
     * Build message text
     * Determine which notification and data to display
     *
     * @return string
     */
    public function getText()
    {
        $errors = array();
        $errors = array_merge(
            $errors,
//            $this->checkNativeTaxRules($storeId),
            $this->checkSoapSupport(),
            $this->checkSslSupport()
        );

        return implode('<br>', $errors);
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        // Critical messages will always show, which is what we want
        return MessageInterface::SEVERITY_CRITICAL;
    }

    /**
     * Check SOAP support
     *
     * @return array
     */
    protected function checkSoapSupport()
    {
        $errors = array();
        if (!class_exists('SoapClient')) {
            $errors[] = __(
                'The PHP class SoapClient is missing. It must be enabled to use this extension. See %1 for details.',
                '<a href="http://www.php.net/manual/en/book.soap.php" target="_blank">http://www.php.net/manual/en/book.soap.php</a>'
            );
        }

        return $errors;
    }

    /**
     * Check SSL support
     *
     * @return array
     */
    protected function checkSslSupport()
    {
        $errors = array();
        if (!function_exists('openssl_sign')) {
            $errors[] = __(
                'SSL must be enabled in PHP to use this extension. Typically, OpenSSL is used but it is not enabled on your server. This may not be a problem if you have some other form of SSL in place. For more information about OpenSSL, see %1.',
                '<a href="http://www.php.net/manual/en/book.openssl.php" target="_blank">http://www.php.net/manual/en/book.openssl.php</a>'
            );
        }

        return $errors;
    }
}
