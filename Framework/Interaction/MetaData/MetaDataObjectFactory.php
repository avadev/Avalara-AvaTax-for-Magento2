<?php
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
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

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
