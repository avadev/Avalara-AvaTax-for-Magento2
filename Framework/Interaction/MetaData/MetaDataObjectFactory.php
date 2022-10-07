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

namespace ClassyLlama\AvaTax\Framework\Interaction\MetaData;

/**
 * Factory class for @see
 * \ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject
 */
class MetaDataObjectFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName;

    protected static $instantiatedObjects = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = '\\ClassyLlama\\AvaTax\\Framework\\Interaction\\MetaData\\MetaDataObject'
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject
     */
    public function create(array $data = array())
    {
        foreach (self::$instantiatedObjects as $objectData) {
            if ($objectData['data'] == $data) {
                return $objectData['object'];
            }
        }

        $object = $this->_objectManager->create($this->_instanceName, $data);
        self::$instantiatedObjects[] = ['data' => $data, 'object' => $object];
        return $object;
    }
}
