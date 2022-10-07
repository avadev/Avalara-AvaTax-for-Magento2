<?php
namespace ClassyLlama\AvaTax\Test\Unit\Block\ViewModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \ClassyLlama\AvaTax\Block\ViewModel\AccountAddExemptionZone
 */
class AccountAddExemptionZoneTest extends TestCase
{
    /**
     * Mock companyRest
     *
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Rest\Company|PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRest;

    /**
     * Mock scopeConfig
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * Mock avaTaxLogger
     *
     * @var \ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger|PHPUnit_Framework_MockObject_MockObject
     */
    private $avaTaxLogger;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Object to test
     *
     * @var \ClassyLlama\AvaTax\Block\ViewModel\AccountAddExemptionZone
     */
    private $testObject;

    /**
     * Main set up method
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        
        $zone1['name'] = "zone1";
        $zone1 = (Object)$zone1;
        $zone2['name'] = "zone2";
        $zone2 = (Object)$zone2;
        $certificateExposureZones['value'] = [$zone1, $zone2];
        $certificateExposureZones = (Object)$certificateExposureZones;

        $this->companyRest = $this->getMockBuilder(\ClassyLlama\AvaTax\Framework\Interaction\Rest\Company::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->companyRest->expects($this->any())
                        ->method('getCertificateExposureZones')
                        ->willReturn($certificateExposureZones);
                                    
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->avaTaxLogger = $this->getMockBuilder(\ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->testObject = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Block\ViewModel\AccountAddExemptionZone::class,
            [
                'companyRest' => $this->companyRest,
                'scopeConfig' => $this->scopeConfig,
                'avaTaxLogger' => $this->avaTaxLogger,
            ]
        );
    }

    /**
     * tests getCertificateExposureZonesJsConfig
     * @test
     * @covers \ClassyLlama\AvaTax\Block\ViewModel\AccountAddExemptionZone::getCertificateExposureZonesJsConfig
     */
    public function testGetCertificateExposureZonesJsConfig()
    {
        $this->testObject->getCertificateExposureZonesJsConfig();
    }

     /**
     * tests isCertificatesAutoValidationDisabled
     * @test
     * @covers \ClassyLlama\AvaTax\Block\ViewModel\AccountAddExemptionZone::isCertificatesAutoValidationDisabled
     */
    public function testIsCertificatesAutoValidationDisabled()
    {
        $this->testObject->isCertificatesAutoValidationDisabled();
    }
}
