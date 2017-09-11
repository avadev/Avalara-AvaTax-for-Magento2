<?php

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'ClassyLlama_AvaTax',
    __DIR__
);

if (defined('BP')) {
    // This path will work when extension is installed via composer or via manual installation
    $vendorPathPrefix = BP . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
} else {
    // This path will work when extension is being run in the context of integration tests, where BP is not defined
    $vendorPathPrefix = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
}

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::LIBRARY,
    'Avalara_AvaTax',
    $vendorPathPrefix . 'avalara' . DIRECTORY_SEPARATOR . 'avatax'
);
