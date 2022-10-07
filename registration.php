<?php
/**
 * @codeCoverageIgnore
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'ClassyLlama_AvaTax',
    __DIR__
);

if (defined('BP')) {
    // This path will work when extension is installed via composer or via manual installation
    $vendorPath = BP . DIRECTORY_SEPARATOR . 'vendor';
} else {
    // This path will work when extension is being run in the context of integration tests, where BP is not defined
    $vendorPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
}

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::LIBRARY,
    'Avalara_AvaTax',
    $vendorPath . DIRECTORY_SEPARATOR . 'avalara' . DIRECTORY_SEPARATOR . 'avataxclient'
);

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Avalara_BaseProvider',
    __DIR__ . DIRECTORY_SEPARATOR . 'BaseProvider'
);
