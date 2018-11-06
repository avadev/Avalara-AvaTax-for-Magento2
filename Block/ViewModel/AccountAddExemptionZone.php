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

namespace ClassyLlama\AvaTax\Block\ViewModel;

use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Helper\UrlSigner;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;

class AccountAddExemptionZone implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company
     */
    protected $companyRest;

    /**
     * @param \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company $companyRest
     */
    public function __construct(\ClassyLlama\AvaTax\Framework\Interaction\Rest\Company $companyRest)
    {
        $this->companyRest = $companyRest;
    }

    /**
     * @return false|string
     * @throws AvataxConnectionException
     */
    public function getCertificateExposureZonesJsConfig()
    {
        $zones = $this->companyRest->getCertificateExposureZones();

        return json_encode(array_map(function($zone) {return $zone->name;}, $zones->value));
    }
}
