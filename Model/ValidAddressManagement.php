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
use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation as ValidationInteraction;
use Magento\Customer\Api\Data\AddressInterface;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;

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
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ValidAddressManagement constructor.
     * @param ValidationInteraction $validationInteraction
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        ValidationInteraction $validationInteraction,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->validationInteraction = $validationInteraction;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function saveValidAddress(AddressInterface $address, $storeId = null) {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        try {
            return $this->validationInteraction->validateAddress($address, $storeId);
        } catch (AvataxConnectionException $e) {
            return __('Address validation connection error')->getText();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
