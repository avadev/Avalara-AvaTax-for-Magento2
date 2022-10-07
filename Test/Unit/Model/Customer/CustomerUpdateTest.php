<?php

namespace ClassyLlama\AvaTax\Test\Unit\Model\Customer;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\Model\Customer\CustomerUpdate;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\DataObject as CustomerUpdateResults;
use Magento\Customer\Api\CustomerRepositoryInterface;
use ClassyLlama\AvaTax\Api\RestCustomerInterface as AvalaraCustomerUpdateService;
use ClassyLlama\AvaTax\Api\CustomerUpdateInterface;
use Magento\Customer\Model\Data\Customer;

/**
 * Class CustomerUpdateTest
 * @package ClassyLlama\AvaTax\Test\Unit\Model\Customer
 */
class CustomerUpdateTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CustomerUpdate
     */
    private $customerUpdate;

    /**
     * @var AvaTaxLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var CustomerUpdateResults|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerUpdateResultsMock;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var AvalaraCustomerUpdateService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $avalaraCustomerUpdateServiceMock;

    /**
     * @var Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void 
    {
        $this->objectManager = new ObjectManager($this);
        $this->loggerMock = $this->createMock(AvaTaxLogger::class);
        $this->customerUpdateResultsMock = $this->createMock(CustomerUpdateResults::class);
        $this->customerRepositoryMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->avalaraCustomerUpdateServiceMock = $this->createMock(AvalaraCustomerUpdateService::class);
        $this->customerMock = $this->createMock(Customer::class);

        $this->customerUpdate = $this->objectManager->getObject(CustomerUpdate::class, [
            'logger' => $this->loggerMock,
            'customerUpdateResults' => $this->customerUpdateResultsMock,
            'customerRepository' => $this->customerRepositoryMock,
            'avalaraCustomerUpdateService' => $this->avalaraCustomerUpdateServiceMock
        ]);
        $this->objectManager->setBackwardCompatibleProperty(
            $this->customerUpdate,
            'customerUpdateResults',
            new \Magento\Framework\DataObject()
        );
        parent::setUp();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\Customer\CustomerUpdate::prepareAvalaraServiceResponse
     */
    public function checkCorrectReturnTypeForPrepareAvalaraServiceResponseMethod()
    {
        $successMessage = 'Success message';
        $errorStatus = false;

        $result = $this->invokeMethod($this->customerUpdate, 'prepareAvalaraServiceResponse', [
            'errorStatus' => $errorStatus,
            'message' => $successMessage
        ]);

        static::assertInstanceOf(CustomerUpdateInterface::class, $result);
        static::assertSame($successMessage, $result->customerUpdateResults->getResult()['message']);
        static::assertSame($errorStatus, $result->customerUpdateResults->getResult()['error_status']);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\Customer\CustomerUpdate::updateCustomerInformation
     */
    public function checkCorrectReturnTypeForUpdateCustomerInformationMethod()
    {
        $customerId = 1;
        $storeId = 8;
        $successMessage = 'success';
        $errorStatus = false;
        $responseData = new \Magento\Framework\DataObject([
                'id' => 99,
                'company_id' => 170111,
                'customer_code' => "3",
                'name' => 'avalara customer name',
                'line_1' => '5th Ave',
                'line_2' => '',
                'city' => 'New York',
                'email_address' => 'email@example.com'
            ]);
        
        $this->customerRepositoryMock->expects(static::atLeastOnce())
            ->method('getById')
            ->willReturn($this->customerMock);
        $this->customerMock->expects(static::atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->avalaraCustomerUpdateServiceMock->expects(static::atLeastOnce())
            ->method('updateCustomer')
            ->willReturn($responseData);

        $result = $this->customerUpdate->updateCustomerInformation($customerId);

        static::assertInstanceOf(CustomerUpdateInterface::class, $result);
        static::assertSame($successMessage, $result->customerUpdateResults->getResult()['message']);
        static::assertSame($errorStatus, $result->customerUpdateResults->getResult()['error_status']);
    }

    /**
     * Call protected/private method of a class
     *
     * @param $object
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    private function invokeMethod(&$object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
