<?php
/*
 * Avalara_BaseProvider
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace ClassyLlama\AvaTax\BaseProvider\Model;

use Magento\Framework\Model\AbstractModel;
use Yandex\Allure\Adapter\Annotation\Description;

/**
 * Log
 * @Description(Logger Model)
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
        $this->_init(\ClassyLlama\AvaTax\BaseProvider\Model\ResourceModel\Log::class);
    }
}
