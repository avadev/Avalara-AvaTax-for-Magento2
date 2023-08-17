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

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\App\ResourceConnection;

/**
 * @codeCoverageIgnore
 */
class TaxCodeSync extends AbstractDb
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * TaxCodeSync constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param ResourceConnection $resourceConnection
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        ResourceConnection $resourceConnection,
        $connectionName = null
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $connectionName);
    }

    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('avatax_tax_code_sync', 'id');
    }

    /**
     * Update product and shipping classes tax codes
     *
     * @param array $taxCodes
     * @param boolean $insert
     * @return int
     */
    public function saveTaxCodes($taxCodes, $insert = false)
    {
        if (empty($taxCodes)) {
            return [];
        }

        if ($taxCodes) {
            $connection = $this->resourceConnection->getConnection();
            if ($insert) {
                $updatedRows = $connection->insertArray(
                    $this->getTable('avatax_tax_code_sync'),
                    array_keys($taxCodes[0]),
                    $taxCodes,
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_IGNORE
                );
            } else {
                // for mass update tax codes with constraint by unique key used insert on duplicate statement
                $updatedRows =$connection->insertOnDuplicate(
                    $this->getTable('avatax_tax_code_sync'),
                    $taxCodes
                );
            }
        }

        return $updatedRows;
    }

    /**
     * Retrieve companyId's config data by path
     *
     * @param string $path
     * @return array
     */
    public function getConfigCompanies($path)
    {
        $connection = $this->getConnection();
        $bind = [':config_path' => $path];
        $select = $connection->select()->from($this->getTable('core_config_data'))->where('path = :config_path');
        $result = [];
        $rowSet = $connection->fetchAll($select, $bind);
        if (!empty($rowSet) && count($rowSet) > 0) {
            foreach ($rowSet as $row) {
                $result[] = [
                    'scope' => $row['scope'],
                    'scope_id' => $row['scope_id'],
                    'isProd' => str_contains($row['path'], 'production') ? 1 : 0,
                    'value' => $row['value']
                ];
            }
        }

        return $result;
    }
}
