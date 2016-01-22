<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Api\ValidAddressManagementInterface;
use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation as ValidationInteraction;
use Magento\Customer\Api\Data\AddressInterface;

/**
 * Class ValidAddressManagement
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidAddressManagement implements ValidAddressManagementInterface
{
    /**
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Address\Validation
     */
    protected $validationInteraction = null;

    /**
     * ValidAddressManagement constructor.
     * @param ValidationInteraction $validationInteraction
     */
    public function __construct(
        ValidationInteraction $validationInteraction
    ) {
        $this->validationInteraction = $validationInteraction;
    }

    /**
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return \Magento\Customer\Api\Data\AddressInterface|string
     */
    public function saveValidAddress(AddressInterface $address) {
        try {
            return $this->validationInteraction->validateAddress($address);
        } catch (\SoapFault $e) {
            return 'Connection Error: ' . $e->getMessage();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
