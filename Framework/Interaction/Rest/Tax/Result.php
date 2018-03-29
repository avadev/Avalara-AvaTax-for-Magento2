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

class Result extends \Magento\Framework\DataObject
{
    /**
     * Get the tax line corresponding with a specific Magento sequence number
     *
     * @param string|int $mageSeqNo
     * @return mixed
     */
    public function getTaxLine($mageSeqNo)
    {
        $result = null;

        if($this->hasRequest() && $this->getRequest()->hasLines() && $this->hasLines())
        {
            $lineNo = null;
            foreach($this->getRequest()->getLines() as $requestLine)
            {
                if ($requestLine->hasLineNumber() && $requestLine->hasMageSequenceNo() && $mageSeqNo == $requestLine->getMageSequenceNo())
                {
                    $lineNo = $requestLine->getLineNumber();
                    break;
                }

            }

            if (!is_null($lineNo)) {
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
}