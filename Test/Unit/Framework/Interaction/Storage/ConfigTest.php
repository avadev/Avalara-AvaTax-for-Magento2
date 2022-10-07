<?php

namespace ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Storage;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\Framework\Interaction\Storage\Config as StorageConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ConfigTest
 * @package ClassyLlama\AvaTax\Test\Unit\Framework\Interaction\Storage
 */
class ConfigTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StorageConfig
     */
    private $storageConfig;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->storageConfig = $this->objectManager->getObject(StorageConfig::class, [
            'scopeConfig' => $this->scopeConfigMock
        ]);
        parent::setUp();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Framework\Interaction\Storage\Config::getResultCacheTtl
     * @dataProvider dataProvider
     */
    public function checkThatWeGetCorrectCacheTtlValue($storageTime)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(StorageConfig::RESULT_CACHE_TTL)
            ->willReturn($storageTime);

        $result = $this->storageConfig->getResultCacheTtl();
        $this->assertSame($result, $storageTime);
        $this->assertIsInt( $result);
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            [15],
            [10],
            [25]
        ];
    }
}
