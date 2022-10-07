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

namespace ClassyLlama\AvaTax\Model\Data;

use ClassyLlama\AvaTax\Api\Data\SDKTokenInterface;
use Magento\Framework\DataObject;

class SDKToken extends DataObject implements SDKTokenInterface
{

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->getData('token');
    }

    /**
     * @return int
     */
    public function getExpires()
    {
        return $this->getData('expires');
    }

    /**
     * @return string
     */
    public function getCustomer()
    {
        return $this->getData('customer');
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    /**
     * @return string
     */
    public function getSdkUrl()
    {
        return $this->getData('sdk_url');
    }
}
