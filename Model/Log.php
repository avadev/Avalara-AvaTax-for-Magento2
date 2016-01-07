<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Matt Johnson <matt.johnson@classyllama.com>
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Log
 *
 * @method string getCreatedAt() getCreatedAt()
 * @method int getStoreId() getStoreId()
 * @method string getLevel() getLevel()
 * @method string getMessage() getMessage()
 * @method string getSource() getSource()
 * @method string getRequest() getRequest()
 * @method string getResult() getResult()
 * @method string getAdditional() getAdditional()
 */
class Log extends AbstractModel
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
