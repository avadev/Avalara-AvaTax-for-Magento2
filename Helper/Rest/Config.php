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

namespace ClassyLlama\AvaTax\Helper\Rest;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use ClassyLlama\AvaTax\Api\RestInterface;

/**
 * Helper for retrieving AvaTax library configuration
 * ANY constants/configuration accessed from AvaTax classes should be referenced via this helper
 */
class Config extends AbstractHelper
{
    /**
     * @param Context $context
     * @param RestInterface $restInteraction
     */
    public function __construct(
        Context $context,
        RestInterface $restInteraction
    ) {
        parent::__construct($context);

        /**
         * This statement MUST be here, so that all classes imported by the AvaTaxClient file will be loaded
         */
        $restInteraction->getClient();
    }

    /**
     * @return string
     */
    public function getDocTypeQuote()
    {
        return \Avalara\DocumentType::C_SALESORDER;
    }

    /**
     * @return string
     */
    public function getDocTypeInvoice()
    {
        return \Avalara\DocumentType::C_SALESINVOICE;
    }

    /**
     * @return string
     */
    public function getDocTypeCreditmemo()
    {
        return \Avalara\DocumentType::C_RETURNINVOICE;
    }

    /**
     * @return string
     */
    public function getDocStatusCommitted()
    {
        return \Avalara\DocumentStatus::C_COMMITTED;
    }

    /**
     * @return string
     */
    public function getAddrTypeFrom()
    {
        return \Avalara\TransactionAddressType::C_SHIPFROM;
    }

    /**
     * @return string
     */
    public function getAddrTypeTo()
    {
        return \Avalara\TransactionAddressType::C_SHIPTO;
    }

    /**
     * @return string
     */
    public function getOverrideTypeDate()
    {
        return \Avalara\TaxOverrideType::C_TAXDATE;
    }

    /**
     * @return string
     */
    public function getTextCaseMixed()
    {
        return \Avalara\TextCase::C_MIXED;
    }

    /**
     * @return array
     */
    public function getErrorSeverityLevels()
    {
        return [
            \Avalara\SeverityLevel::C_ERROR,
            \Avalara\SeverityLevel::C_EXCEPTION,
        ];
    }

    /**
     * @return array
     */
    public function getWarningSeverityLevels()
    {
        return [
            \Avalara\SeverityLevel::C_WARNING,
        ];
    }
}