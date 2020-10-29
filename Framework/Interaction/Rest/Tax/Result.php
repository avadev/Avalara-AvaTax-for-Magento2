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

namespace ClassyLlama\AvaTax\Framework\Interaction\Rest\Tax;

use Magento\Framework\Exception\LocalizedException;

class Result extends \Magento\Framework\DataObject
{
    /**
     * @var array
     */
    protected $rates = [];

    /**
     * Get the tax line corresponding with a specific Magento sequence number
     *
     * @param string|int $mageSeqNo
     * @return null|\Magento\Framework\DataObject
     */
    public function getTaxLine($mageSeqNo)
    {
        $result = null;

        if($this->hasRequest() && $this->getRequest()->hasLines() && $this->hasLines())
        {
            $lineNo = null;
            // First, look through the original request items for the matching sequence number, and fetch the line number that was associated when request was sent
            /** @var \Magento\Framework\DataObject $requestLine */
            foreach($this->getRequest()->getLines() as $requestLine)
            {
                if ($requestLine->hasNumber() && $requestLine->hasMageSequenceNo() && $mageSeqNo == $requestLine->getMageSequenceNo())
                {
                    $lineNo = $requestLine->getNumber();
                    break;
                }

            }

            // Look for the matching item from the response
            if (!is_null($lineNo)) {
                /** @var \Magento\Framework\DataObject $taxLine */
                foreach ($this->getLines() as $taxLine) {
                    if ($taxLine->hasLineNumber() && $lineNo == $taxLine->getLineNumber()) {
                        $result = $taxLine;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get the total tax rate for a specific line item
     *
     * @param int|\Magento\Framework\DataObject $line
     * @return float
     * @throws LocalizedException
     */
    public function getLineRate($line)
    {
        if (!is_object($line)) {
            $line = $this->getTaxLine($line);
        }

        if (is_null($line) || !$line->hasDetails()) {
            throw new \Magento\Framework\Exception\InputException(__('Could not get rate details for tax line'));
        }

        if (!isset($this->rates[$line->getLineNumber()])) {
            $rate = 0;
            foreach ($line->getDetails() as $detail) {
                if ($detail->hasRate()) {
                    $rate += $detail->getRate();
                }
            }

            $this->rates[$line->getLineNumber()] = $rate;
        }

        return $this->rates[$line->getLineNumber()];
    }
}