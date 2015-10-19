<?php

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'ClassyLlama_AvaTax',
    __DIR__
);

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::LIBRARY,
    'Avalara_AvaTax',
    __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'avalara' . DIRECTORY_SEPARATOR . 'avatax' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'AvaTax'
);
