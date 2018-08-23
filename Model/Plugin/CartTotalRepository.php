<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ClassyLlama\AvaTax\Model\Plugin;

use Magento\Quote\Api\Data\TotalsExtensionFactory;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Cart\CartTotalRepository as TotalRepository;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;

class CartTotalRepository
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var TotalsExtensionFactory
     */
    protected $totalsExtensionFactory;

    /**
     * @param TotalsExtensionFactory  $totalsExtensionFactory
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        TotalsExtensionFactory $totalsExtensionFactory,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->totalsExtensionFactory = $totalsExtensionFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param TotalRepository $subject
     * @param TotalsInterface $totals
     * @param int             $cartId
     *
     * @return TotalsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGet(TotalRepository $subject, TotalsInterface $totals, $cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $addressesWithMessages = 0;
        $messages = [[]];

        foreach($quote->getAllAddresses() as $address) {
            if(!$address->hasAvataxMessages()) {
                continue;
            }

            $message = json_decode($address->getAvataxMessages());

            if(is_array($message)) {
                $messages[] = $message;
                $addressesWithMessages++;
            }
        }

        /** @var \Magento\Quote\Api\Data\TotalsExtensionInterface $extensionAttributes */
        $extensionAttributes = $totals->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->totalsExtensionFactory->create();
        }

        $extensionAttributes->setAvataxMessages(array_merge(...$messages));
        $totals->setExtensionAttributes($extensionAttributes);

        return $totals;
    }
}
