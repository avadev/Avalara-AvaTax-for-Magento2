<?php

namespace ClassyLlama\AvaTax\Model\Catalog\CustomOption\CustomOptionProcessor;

class Plugin
{
    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory = null;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository = null;

    /**
     * @var \ClassyLlama\AvaTax\Model\Config
     */
    protected $config = null;

    public function __construct(
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \ClassyLlama\AvaTax\Model\Config $config
    ) {
        $this->objectFactory = $objectFactory;
        $this->cartRepository = $cartRepository;
        $this->config = $config;
    }

    /**
     * Add avatax_attributes to buy request
     * TODO: Figure out why this is not working
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param \Magento\Catalog\Model\CustomOptions\CustomOptionProcessor $customOptionProcessor
     * @param \Closure $proceed
     * @param \Magento\Quote\Api\Data\CartItemInterface $cartItem
     * @return \Magento\Framework\DataObject|null
     */
    public function aroundConvertToBuyRequest(
        \Magento\Catalog\Model\CustomOptions\CustomOptionProcessor $customOptionProcessor,
        \Closure $proceed,
        \Magento\Quote\Api\Data\CartItemInterface $cartItem
    ) {
        $result = $proceed($cartItem);
        $cart = $this->cartRepository->get($cartItem->getQuoteId());
        $storeId = $cart->getStoreId();
        $requestData = ['avatax_attributes' => []];
        $ref1 = $cartItem->getProduct()->getData($this->config->getRef1($storeId));
        if ($ref1) {
            $requestData['avatax_attributes']['ref1'] = $ref1;
        }
        $ref2 = $cartItem->getProduct()->getData($this->config->getRef2($storeId));
        if ($ref2) {
            $requestData['avatax_attributes']['ref2'] = $ref2;
        }
        if (is_null($result) && empty($requestData['avatax_attributes'])) {
            return null;
        } elseif (is_null($result)) {
            return $this->objectFactory->create($requestData);
        } else {
            /** @var $result \Magento\Framework\DataObject */
            $result->addData($requestData);
            return $result;
        }
    }
}