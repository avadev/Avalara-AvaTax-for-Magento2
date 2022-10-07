<?php

namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Storage;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\Framework\Interaction\Storage\Session as StorageSession;
use Magento\Framework\Session\Storage;

/**
 * Class SessionTest
 * @package ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Storage
 */
class SessionTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StorageSession
     */
    private $storageSession;

    /**
     * @var Storage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storageMock;

    /**
     * @var string
     */
    private $namespace = 'namespace';

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->storageSession = $this->objectManager->getObject(StorageSession::class);

        $this->storageMock = $this->getMockBuilder(Storage::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData','setData'])
            ->getMock();

        $this->objectManager->setBackwardCompatibleProperty(
            $this->storageSession,
            'storage',
            $this->storageMock
        );
        parent::setUp();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Storage\Session::getResults
     */
    public function checkThatWeCanGetResultsFromTheSessionCache()
    {
        /** @var array $storageData */
        $storageData = $this->getMockData();

        $this->storageMock->expects(static::atLeastOnce())
            ->method('getData')
            ->with($this->namespace)
            ->willReturn($storageData);

        /** @var array $cacheResult */
        $cacheResult = $this->storageSession->getResults($this->namespace);
        $this->assertSame($cacheResult, $storageData);
        $this->assertArrayHasKey('storage_data', $cacheResult);

        $cacheResultNoNamespace = $this->storageSession->getResults('');
        $this->assertSame($cacheResultNoNamespace, []);
        $this->assertArrayNotHasKey('storage_data', $cacheResultNoNamespace);
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Storage\Session::setResults
     */
    public function checkThatWeCanSetResultsIntoTheSessionCache()
    {
        /** @var array $storageData */
        $storageData = $this->getMockData();

        $this->storageMock->expects(static::atLeastOnce())
            ->method('setData')
            ->with($this->namespace, $storageData)
            ->willReturnSelf();

        /** @var StorageSession $setCacheResults */
        $setCacheResults = $this->storageSession->setResults($this->namespace, $storageData);
        $this->assertInstanceOf(StorageSession::class, $setCacheResults);

        /** @var StorageSession $setCacheResultsNoNamespace */
        $setCacheResultsNoNamespace = $this->storageSession->setResults('', $storageData);
        $this->assertInstanceOf(StorageSession::class, $setCacheResultsNoNamespace);
    }

    /**
     * @return array
     */
    private function getMockData(): array
    {
        return [
            'storage_data' => 'some data'
        ];
    }
}
