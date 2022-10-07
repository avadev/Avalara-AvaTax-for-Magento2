<?php

namespace ClassyLlama\AvaTax\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ClassyLlama\AvaTax\Model\Certificates;
use Magento\Backend\Model\Auth\Session as AuthSession;
use ClassyLlama\AvaTax\Helper\CertificateHelper;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\DataObject;

/**
 * Class СertificatesTest
 * @package ClassyLlama\AvaTax\Test\Unit\Model
 */
class CertificatesTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Certificates
     */
    private $certificates;

    /**
     * @var AuthSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authSessionMock;

    /**
     * @var CertificateHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $certificateHelperMock;

    /**
     * @var AvaTaxLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $avaTaxLoggerMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void 
    {
        $this->objectManager = new ObjectManager($this);
        $this->authSessionMock = $this->createMock(AuthSession::class);
        $this->certificateHelperMock = $this->createMock(CertificateHelper::class);
        $this->avaTaxLoggerMock = $this->createMock(AvaTaxLogger::class);
        $this->certificates = $this->objectManager->getObject(Certificates::class, [
            'authSession' =>  $this->authSessionMock,
            'certificateHelper' => $this->certificateHelperMock,
            'avaTaxLogger' => $this->avaTaxLoggerMock
        ]);
        parent::setUp();
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\Certificates::getCertificatesList
     */
    public function checkCorrectReturnTypeWhileGettingCertificatesList()
    {
        $userId = 1;
        $viewUrl = 'example/view_url';
        $deleteUrl = 'example/delete_url';

        $this->certificateHelperMock->expects(static::atLeastOnce())
            ->method('getCertificates')
            ->willReturn($this->getMockData());
        $this->certificateHelperMock->expects(static::atLeastOnce())
            ->method('getCertificateUrl')
            ->willReturn($viewUrl);
        $this->certificateHelperMock->expects(static::atLeastOnce())
            ->method('getCertificateDeleteUrl')
            ->willReturn($deleteUrl);

        /** @var array<int, DataObject> $result */
        $result = $this->certificates->getCertificatesList($userId);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(DataObject::class, $result[0]);
        $this->assertInstanceOf(DataObject::class, $result[1]);
        $this->assertSame($viewUrl, $result[0]->getData('certificate_url'));
        $this->assertSame($deleteUrl, $result[0]->getData('certificate_delete_url'));
        $this->assertSame($viewUrl, $result[1]->getData('certificate_url'));
        $this->assertSame($deleteUrl, $result[1]->getData('certificate_delete_url'));
    }

    /**
     * @test
     * @covers \ClassyLlama\AvaTax\Model\Certificates::getCertificatesList
     */
    public function checkCorrectReturnTypeInCaseOfError()
    {
        $userId = 1;

        $this->certificateHelperMock->expects(static::atLeastOnce())
            ->method('getCertificates')
            ->willThrowException(new \Exception('Error happened'));

        /** @var array $result */
        $result = $this->certificates->getCertificatesList($userId);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /**
     * Get mock data
     *
     * @return array
     */
    public function getMockData(): array
    {
        return [
            new DataObject([
                'id' => 5,
                'company_id' => 170111,
                'signed_date' => '2018-04-23',
                'expiration_date' => '2021-04-23',
                'filename' => '71011_auto_5.pdf',
                'document_exists' => true,
                'valid' => true,
                'verified' => false,
                'exempt_percentage' => 100.0,
                'is_single_certificate' => false,
                'exemption_number' => '388383838383',
                'validated_exemption_reason' => new DataObject([
                    'id' => 71,
                    'name' => 'RESALE'
                ]),
                'exemption_reason' => new DataObject([
                    'id' => 71,
                    'name' => 'RESALE'
                ]),
                'status' => 'COMPLETE',
                'created_date' => '2018-04-23T20:49:40.860677',
                'modified_date' => '2018-06-08',
                'page_count' => 0,
                'exposure_zone' => new DataObject([
                    'id' => 127,
                    'name' => 'Idaho',
                    'tag' => 'EZ_US_ID',
                    'description' => 'Idaho Sales Tax',
                    'region' => 'ID',
                    'country' => 'US'
                ])
            ]),
            new DataObject([
                'id' => 35,
                'company_id' => 170111,
                'signed_date' => '2018-07-04',
                'expiration_date' => '2021-07-04',
                'filename' => '71011_auto_35.pdf',
                'document_exists' => true,
                'valid' => true,
                'verified' => false,
                'exempt_percentage' => 100.0,
                'is_single_certificate' => false,
                'exemption_number' => 'asd',
                'validated_exemption_reason' => new DataObject([
                    'id' => 71,
                    'name' => 'RESALE'
                ]),
                'exemption_reason' => new DataObject([
                    'id' => 71,
                    'name' => 'RESALE'
                ]),
                'status' => 'COMPLETE',
                'created_date' => '2018-07-04T08:49:35.232304',
                'modified_date' => '2018-07-04',
                'page_count' => 1,
                'exposure_zone' => new DataObject([
                    'id' => 127,
                    'name' => 'Idaho',
                    'tag' => 'EZ_US_ID',
                    'description' => 'Idaho Sales Tax',
                    'region' => 'ID',
                    'country' => 'US'
                ])
            ])
        ];
    }
}
