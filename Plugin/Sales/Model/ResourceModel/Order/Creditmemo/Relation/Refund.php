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

namespace ClassyLlama\AvaTax\Plugin\Sales\Model\ResourceModel\Order\Creditmemo\Relation;

use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\CreditmemoInterface;

/**
 * Class Refund
 *
 * Plugin for \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Relation\Refund
 */
class Refund
{
    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Relation\Refund $subject
     * @param \Closure $proceed
     * @param AbstractModel $object
     * @return void
     * @throws \Exception
     */
    public function aroundProcessRelation(
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Relation\Refund $subject,
        \Closure $proceed,
        AbstractModel $object
    ) {
        /*
         * The \Magento\Framework\Model\ResourceModel\Db\AbstractDb save() method makes a call to
         * processRelations() on the credit memo ResourceModel when the credit memo object is saved
         * $this->objectRelationProcessor->validateDataIntegrity($this->getMainTable(), $object->getData());
         */

        $avataxDataChanges = false;
        $prepareDataChanges = false;

        // Check to see if object is a credit memo
        if ($object instanceof CreditmemoInterface) {

            // Check to see if dataHasChangedFor AvaTax specific fields
            if (
                $object->dataHasChangedFor('avatax_is_unbalanced') ||
                $object->dataHasChangedFor('base_avatax_tax_amount')
            ) {
                $avataxDataChanges = true;
            }

            /**
             * Rather than looping through all attributes and checking for changes other than the
             * avatax fields, we will explicity check the fields used by the processRelations() methods
             * that way it will minimally impact any customizations made by any 3rd party modules
             * that would affect the credit memo. One of the problems with checking all attributes
             * is that fields like 'items' which is an object can show up as indicating a change when
             * checked specifically, as would likely other attributes that are stored as objects rather
             * that primative values. Also there are attributes like 'updated_at' that change each time.
             */

            // Check to see if dataHasChangedFor various fields used by processRelations()
            //  $subject->prepareOrder()
            //  $subject->prepareInvoice()
            //  $subject->preparePayment()
            if (
                $object->dataHasChangedFor('base_grand_total') ||
                $object->dataHasChangedFor('grand_total') ||
                $object->dataHasChangedFor('base_subtotal') ||
                $object->dataHasChangedFor('subtotal') ||
                $object->dataHasChangedFor('base_tax_amount') ||
                $object->dataHasChangedFor('tax_amount') ||
                $object->dataHasChangedFor('base_discount_tax_compensation_amount') ||
                $object->dataHasChangedFor('discount_tax_compensation_amount') ||
                $object->dataHasChangedFor('base_shipping_amount') ||
                $object->dataHasChangedFor('shipping_amount') ||
                $object->dataHasChangedFor('base_shipping_tax_amount') ||
                $object->dataHasChangedFor('shipping_tax_amount') ||
                $object->dataHasChangedFor('adjustment_positive') ||
                $object->dataHasChangedFor('base_adjustment_positive') ||
                $object->dataHasChangedFor('adjustment_negative') ||
                $object->dataHasChangedFor('base_adjustment_negative') ||
                $object->dataHasChangedFor('discount_amount') ||
                $object->dataHasChangedFor('base_discount_amount') ||
                $object->dataHasChangedFor('base_cost')
            ) {
                $prepareDataChanges = true;
            }
        }

        if ($avataxDataChanges == true && $prepareDataChanges == false) {
            // Bypass subjects call to processRelations() and return

            // This should only end up affecting credit memos that are saved during queue processing
            // ClassyLlama\AvaTax\Model\Queue\Processing::updateAdditionalEntityAttributes()

            /*
             * When processRelation() is called as a credit memo is being saved
             * it will make calls to prepareOrder(), prepareInvoice(), and preparePayment()
             * and those methods will alter the related objects trying to adjust for the amount
             * affected by the credit memo in the related objects and save those related objects,
             * so if save is called on a credit memo more than once like we are doing in
             * ClassyLlama\AvaTax\Model\Queue\Processing it will make the adjustment on related
             * objects twice which is not good, so we have a check here to bypass the call to
             * processRelations() so that our additional field will get saved to the credit memo object
             * via the repository but not end up affecting the related objects in a negative manor.
             * */

            return;
        } else {
            // Proceed with subjects call to processRelations()
            $proceed($object);
            return;
        }
    }
}
