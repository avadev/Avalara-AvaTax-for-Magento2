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
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model\ResourceModel;

class Config extends \Magento\Config\Model\ResourceModel\Config
{
    /**
     * @param array $configPaths
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfigCount($configPaths = [])
    {
        if (\count($configPaths) === 0) {
            return 0;
        }

        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), 'count(*) as count')->where(
            'path IN (?)',
            $configPaths
        );

        return (int)$connection->fetchOne($select);
    }
}