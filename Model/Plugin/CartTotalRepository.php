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
     * Takes any messages from AvaTax stored on the quote addresses from collect totals and places them on totals
     * so that the data is retrievable from the frontend API
     *
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
