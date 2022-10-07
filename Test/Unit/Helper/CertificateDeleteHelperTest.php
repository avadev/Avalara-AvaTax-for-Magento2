<?php
/*
 *
 * ClassyLlama_AvaTax
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
namespace ClassyLlama\AvaTax\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class CertificateDeleteHelperTest
 * @covers \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper
 * @package ClassyLlama\AvaTax\Test\Unit\Helper
 */
class CertificateDeleteHelperTest extends TestCase
{
    /**
     * Mock context
     *
     * @var \Magento\Framework\App\Helper\Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * Mock request
     *
     * @var \Magento\Framework\App\RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * Mock customerRepository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * Mock customerRest
     *
     * @var \ClassyLlama\AvaTax\Api\RestCustomerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRest;

    /**
     * Mock dataObjectFactoryInstance
     *
     * @var \Magento\Framework\DataObject|PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactoryInstance;

    /**
     * Mock dataObjectFactory
     *
     * @var \Magento\Framework\DataObjectFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactory;

    /**
     * Mock taxCache
     *
     * @var \ClassyLlama\AvaTax\Model\TaxCache|PHPUnit_Framework_MockObject_MockObject
     */
    private $taxCache;

    /**
     * Mock messageManager
     *
     * @var \Magento\Framework\Message\ManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper
     */
    private $testObject;

    /**
     * Setup
     * @covers \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper::__construct
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->context = $this->createPartialMock(\Magento\Framework\App\Helper\Context::class, ['getScopeConfig']);
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRest = $this->getMockBuilder(\ClassyLlama\AvaTax\Api\RestCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectFactoryInstance = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectFactory = $this->getMockBuilder(\Magento\Framework\DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectFactory->method('create')->willReturn($this->dataObjectFactoryInstance);
        $this->taxCache = $this->getMockBuilder(\ClassyLlama\AvaTax\Model\TaxCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->testObject = $this->objectManager->getObject(
        \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper::class,
            [
                'context' => $this->context,
                'request' => $this->request,
                'customerRepository' => $this->customerRepository,
                'customerRest' => $this->customerRest,
                'dataObjectFactory' => $this->dataObjectFactory,
                'taxCache' => $this->taxCache,
                'messageManager' => $this->messageManager,
            ]
        );
    }

    /**
     * tests certificate delete
     * @test
     * @covers \ClassyLlama\AvaTax\Helper\CertificateDeleteHelper::delete
     */
    public function testDelete()
    {
        // code
    }
}
