<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Framework\Interaction\Request;

use ClassyLlama\AvaTax\Api\Framework\Interaction\Request\LineBuilderInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use ClassyLlama\AvaTax\Framework\Interaction\Line as InteractionLine;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException;
use Magento\Framework\DataObject;

/**
 * Class LineBuilder
 * @package ClassyLlama\AvaTax\Framework\Interaction\Request
 */
class LineBuilder implements LineBuilderInterface
{

    /**
     * @var array
     */
    private $lines = [];

    /**
     * @var InteractionLine
     */
    private $interactionLine;

    /**
     * LineBuilder constructor.
     * @param InteractionLine $interactionLine
     */
    public function __construct(InteractionLine $interactionLine)
    {
        $this->interactionLine = $interactionLine;
    }

    /**
     * Line builder
     *
     * @param CreditmemoInterface $creditmemo
     * @param OrderItem[] $orderItems
     * @param bool $flag
     * @return array
     * @throws ValidationException
     */
    public function build(CreditmemoInterface $creditmemo, array $orderItems = [], bool $flag = false): array
    {
        /** @var CreditmemoItem $creditmemoItem */
        foreach ($creditmemo->getItems() as $creditmemoItem) {
            if (array_key_exists((int)$creditmemoItem->getProductId(), $orderItems)) {
                if (($line = $this->interactionLine->getLine($creditmemoItem)) && ($line instanceof DataObject)) {
                    $this->lines[] = $line;
                }
            }
        }
        // get additional lines
        $this->lines = array_merge($this->lines, $this->getAdditionalLines($creditmemo, $flag));
        /**
         * remove empty [] in case we got an error
         */
        $this->lines = array_filter($this->lines, function ($v, $k) {
            return !empty($v);
        }, ARRAY_FILTER_USE_BOTH);

        return $this->lines;
    }

    /**
     * Get additional lines
     *
     * @param CreditmemoInterface $creditmemo
     * @param bool $flag
     * @return array
     * @throws ValidationException
     */
    private function getAdditionalLines(CreditmemoInterface $creditmemo, bool $flag = false): array
    {
        return [
            //shipping line
            ($line = $this->interactionLine->getShippingLine($creditmemo, $flag)) && ($line instanceof DataObject) ? $line : [],
            //getGiftWrapItemsLine
            ($line = $this->interactionLine->getGiftWrapItemsLine($creditmemo, $flag)) && ($line instanceof DataObject) ? $line : [],
            //getGiftWrapOrderLine
            ($line = $this->interactionLine->getGiftWrapOrderLine($creditmemo, $flag)) && ($line instanceof DataObject) ? $line : [],
            //getGiftWrapCardLine
            ($line = $this->interactionLine->getGiftWrapCardLine($creditmemo, $flag)) && ($line instanceof DataObject) ? $line : [],
            //getPositiveAdjustmentLine
            ($line = $this->interactionLine->getPositiveAdjustmentLine($creditmemo)) && ($line instanceof DataObject) ? $line : [],
            //getNegativeAdjustmentLine
            ($line = $this->interactionLine->getNegativeAdjustmentLine($creditmemo)) && ($line instanceof DataObject) ? $line : [],
        ];
    }
}
