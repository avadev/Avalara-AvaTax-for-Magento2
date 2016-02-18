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
     * {@inheritDoc}
     */
    public function saveValidAddress(AddressInterface $address, $storeId) {
        try {
            return $this->validationInteraction->validateAddress($address, $storeId);
        } catch (\SoapFault $e) {
            return 'Connection Error: ' . $e->getMessage();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
