<?php

namespace ClassyLlama\AvaTax\Model;

/**
 * Log
 *
 * @method string getCreatedAt() getCreatedAt()
 * @method int getStoreId() getStoreId()
 * @method string getLevel() getLevel()
 * @method string getMessage() getMessage()
 * @method string getSource() getSource()
 * @method string getRequest() getRequest()
 * @method string getResponse() getResponse()
 * @method string getAdditional() getAdditional()
 */
class Log extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ClassyLlama\AvaTax\Model\ResourceModel\Log');
    }
}
