<?php
declare(strict_types=1);

namespace ClassyLlama\AvaTax\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class BatchQueueTransaction
 *
 * @package ClassyLlama\AvaTax\Model\ResourceModel
 */
class BatchQueueTransaction extends AbstractDb
{
    /**
     * BatchQueueTransaction constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('avatax_batch_queue', 'entity_id');
    }
}
