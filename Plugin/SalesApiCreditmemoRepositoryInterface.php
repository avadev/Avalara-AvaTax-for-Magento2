<?php

namespace ClassyLlama\AvaTax\Plugin;

use ClassyLlama\AvaTax\Model\Config;
use ClassyLlama\AvaTax\Model\QueueFactory;
USE ClassyLlama\AvaTax\Model\Queue;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SalesApiCreditmemoRepositoryInterface
{
    const XML_PATH_AVATAX_QUEUE_SUBMIT_ENABLED = 'tax/avatax/enabled';

    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $avaTaxConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $avaTaxConfig,
        QueueFactory $queueFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->queueFactory = $queueFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param null $store
     * @return bool
     */
    public function queueSubmitEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_QUEUE_SUBMIT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }





    /**
     * Save gift message
     *
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $subject
     * @param callable $proceed
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    public function aroundSave(
        \Magento\Sales\Api\CreditmemoRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo
    ) {
        // TODO: Instead of wrapping around the Repository, try wrapping the ResourceModel
        // TODO: Save the avatax_creditmemo from the extensionAttributes to it's own repository

        /* @var \Magento\Sales\Api\Data\CreditmemoExtension $extensionAttributes */
//        $extensionAttributes = $creditmemo->getExtensionAttributes();
//        if ($extensionAttributes != null && (
//                $extensionAttributes->getAvataxIsUnbalanced() != null ||
//                $extensionAttributes->getBaseAvataxTaxAmount() != null
//            )
//        ) {
//            $creditmemo->setAvataxIsUnbalanced($extensionAttributes->getAvataxIsUnbalanced());
//            $creditmemo->setBaseAvataxTaxAmount($extensionAttributes->getBaseAvataxTaxAmount());
//        }

        /** @var \Magento\Sales\Api\Data\CreditmemoInterface $resultCreditmemo */
        $resultCreditmemo = $proceed($creditmemo);
        //$resultCreditmemo = $this->saveAvataxIsUnbalanced($resultCreditmemo);
        //$resultCreditmemo = $this->saveOrderItemGiftMessage($resultCreditmemo);

        return $resultCreditmemo;
    }






    /**
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $result
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    public function afterSave(
        \Magento\Sales\Api\CreditmemoRepositoryInterface $subject,
        \Magento\Sales\Api\Data\CreditmemoInterface $result
    ) {
        // Queue the invoice to be sent to AvaTax
        if ($this->avaTaxConfig->isModuleEnabled() && $this->queueSubmitEnabled()) {

            // TODO: Confirm new credit memos are going to still get queue records
            // This isObjectNew() check is inteneded to avoid creating queue records
            // when a credit memo is saved during processing to avatax when we add
            // the response fields back to the credit memo.
            if ($result->isObjectNew())
            {
                //$entityTypeCode = $result->getEntityType();
                $entityType = $this->eavConfig->getEntityType(Queue::ENTITY_TYPE_CODE_CREDITMEMO);

                /** @var Queue $queue */
                $queue = $this->queueFactory->create();

                $queue->setData('store_id', $result->getStoreId());
                $queue->setData('entity_type_id', $entityType->getEntityTypeId());
                $queue->setData('entity_type_code', Queue::ENTITY_TYPE_CODE_CREDITMEMO);
                $queue->setData('entity_id', $result->getEntityId());
                $queue->setData('increment_id', $result->getIncrementId());
                $queue->setData('queue_status', \ClassyLlama\AvaTax\Model\Queue::QUEUE_STATUS_PENDING);
                $queue->setData('attempts', 0);
                $queue->save();
            }
        }

        return $result;
    }
}