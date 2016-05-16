<?php

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'ClassyLlama_AvaTax',
    __DIR__
);

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::LIBRARY,
    'Avalara_AvaTax',
    BP . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'avalara' . DIRECTORY_SEPARATOR . 'avatax'
);
