<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Framework\Interaction\Request;

use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\AddressBuilderInterface;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use ClassyLlama\AvaTax\Framework\Interaction\Address as InteractionAddress;
use ClassyLlama\AvaTax\Framework\Interaction\Address as FrameworkInteractionAddress;
use ClassyLlama\AvaTax\Helper\Config as AvaTaxHelperConfig;
use Magento\Sales\Api\Data\OrderAddressInterface as OrderAddress;
use ClassyLlama\AvaTax\Helper\Rest\Config as AvaTaxHelperRestConfig;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;

/**
 * Class AddressBuilder
 * @package ClassyLlama\AvaTax\Framework\Interaction\Request
 */
class AddressBuilder implements AddressBuilderInterface
{
    /**
     * @var InteractionAddress
     */
    private $interactionAddress;

    /**
     * @var FrameworkInteractionAddress
     */
    private $frameworkInteractionAddress;

    /**
     * @var AvaTaxHelperConfig
     */
    private $avataxHelperConfig;

    /**
     * @var AvaTaxHelperRestConfig
     */
    private $avaTaxHelperRestConfig;

    /**
     * @var AvaTaxLogger
     */
    private $avaTaxLogger;

    /**
     * AddressBuilder constructor.
     * @param AvaTaxLogger $avaTaxLogger
     * @param AvaTaxHelperRestConfig $avaTaxHelperRestConfig
     * @param FrameworkInteractionAddress $interactionAddress
     * @param FrameworkInteractionAddress $frameworkInteractionAddress
     * @param AvaTaxHelperConfig $avataxHelperConfig
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        AvaTaxHelperRestConfig $avaTaxHelperRestConfig,
        InteractionAddress $interactionAddress,
        FrameworkInteractionAddress $frameworkInteractionAddress,
        AvaTaxHelperConfig $avataxHelperConfig
    ) {
        $this->interactionAddress = $interactionAddress;
        $this->frameworkInteractionAddress = $frameworkInteractionAddress;
        $this->avataxHelperConfig = $avataxHelperConfig;
        $this->avaTaxHelperRestConfig = $avaTaxHelperRestConfig;
        $this->avaTaxLogger = $avaTaxLogger;
    }

    /**
     * Address builder
     *
     * @param Order $order
     * @param int $storeId
     * @return array
     */
    public function build(Order $order, int $storeId): array
    {
        try {
            /** @var OrderAddress $address */
            $orderAddress = (!$order->getIsVirtual()) ? $order->getShippingAddress() : $order->getBillingAddress();
            /** @var DataObject $addressTypeTo */
            $addressTypeTo = $this->interactionAddress->getAddress($orderAddress);
            /** @var DataObject $addressTypeFrom */
            $addressTypeFrom = $this->frameworkInteractionAddress->getAddress($this->avataxHelperConfig->getOriginAddress($storeId));

            return [
                $this->avaTaxHelperRestConfig->getAddrTypeTo() => $addressTypeTo,
                $this->avaTaxHelperRestConfig->getAddrTypeFrom() => $addressTypeFrom
            ];

        } catch (\Throwable $exception) {
            $this->avaTaxLogger->error($exception->getMessage(), [
                'class' => self::class,
                'trace' => $exception->getTraceAsString()
            ]);
            return [];
        }
    }
}
