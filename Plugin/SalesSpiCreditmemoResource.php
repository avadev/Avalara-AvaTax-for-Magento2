<?php

namespace ClassyLlama\AvaTax\Plugin;

use ClassyLlama\AvaTax\Model\Config;

class SalesSpiCreditmemoResource
{
    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var \ClassyLlama\AvaTax\Model\CreditmemoRepository
     */
    protected $avaTaxCreditmemoRepository;

    /**
     * @param Config $avaTaxConfig
     * @param \ClassyLlama\AvaTax\Model\CreditmemoRepository $avaTaxCreditmemoRepository
     */
    public function __construct(
        Config $avaTaxConfig,
        \ClassyLlama\AvaTax\Model\CreditmemoRepository $avaTaxCreditmemoRepository
    ) {
        $this->avaTaxConfig = $avaTaxConfig;
        $this->avaTaxCreditmemoRepository = $avaTaxCreditmemoRepository;
    }

    /**
     * Plugin for
     *
     * @param \Magento\Sales\Model\Spi\CreditmemoResourceInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\Model\AbstractModel $creditmemo
     * @return \Magento\Sales\Model\Spi\CreditmemoResourceInterface
     */
    public function aroundSave(
        \Magento\Sales\Model\Spi\CreditmemoResourceInterface $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $creditmemo
    ) {
        /** @var \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo */

        /** @var \Magento\Sales\Model\Spi\CreditmemoResourceInterface $resultCreditmemo */
        $resultCreditmemo = $proceed($creditmemo);




        // Save AvaTax Credit Memo extension attributes

        // TODO: Check config to see if this should be enabled
        if (true == false)
        {
           return $resultCreditmemo;
        }

        // check to see if any extension attributes exist
        /* @var \Magento\Sales\Api\Data\CreditmemoExtension $extensionAttributes */
        $extensionAttributes = $creditmemo->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return $resultCreditmemo;

        }

        // check to see if any values are set on the avatax extension attributes
        $avataxCreditmemo = $extensionAttributes->getAvataxExtension();
        if ($avataxCreditmemo == null) {
            return $resultCreditmemo;
        }

        // save the AvaTax Credit Memo
        $this->avaTaxCreditmemoRepository->save($avataxCreditmemo);

        return $resultCreditmemo;
    }
}