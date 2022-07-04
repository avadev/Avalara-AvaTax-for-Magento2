<?php
/*
 *
 * Avalara_BaseProvider
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

namespaceClassyLlama\AvaTax\BaseProvider\Test\Unit\Framework\Rest;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ApiClient
 * @covers \ClassyLlama\AvaTax\BaseProvider\Framework\Rest\ApiClient
 * @packageClassyLlama\AvaTax\BaseProvider\Framework
 */
class ApiClientTest extends TestCase
{
	protected function setUp(): void
    {
		$this->objectManager = new ObjectManager($this);
        
        $response = [
            "description" => "Request processed successfully",
            "error" => "" 
        ];
        $response = json_encode($response);
        $this->clientResponseMock = $this->getMockBuilder(\Psr\Http\Message\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientResponseMock->expects($this->any())
            ->method('getBody')
            ->willReturn($response); 
        $this->clientMock = $this->getMockBuilder(\GuzzleHttp\Client::class)
            ->disableOriginalConstructor()
            ->getMock(); 
        $this->clientMock->expects($this->any())
            ->method('request')
            ->willReturn($this->clientResponseMock);
        $this->clientFactoryMock = $this->getMockBuilder(\GuzzleHttp\ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->clientMock);
            
        $this->responseFactoryMock = $this->getMockBuilder(\GuzzleHttp\Psr7\ResponseFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiClient = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\BaseProvider\Framework\Rest\ApiClient::class,
            [
                'clientFactory' => $this->clientFactoryMock,
                'responseFactory' => $this->responseFactoryMock
            ]
        );

		parent::setUp();
    }

    /**
     * tests setClient
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Framework\Rest\ApiClient::setClient
     */
    public function testSetClient()
    {
        $endPointBaseUrl = 'api/logger';
        $this->assertIsObject($this->apiClient->setClient($endPointBaseUrl));
    }

    /**
     * tests addtionalHeaders
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Framework\Rest\ApiClient::addtionalHeaders
     */
    public function testAddtionalHeaders()
    {
        $params['api-xp'] = 'test';
        $this->assertIsObject($this->apiClient->addtionalHeaders($params));
    }

    /**
     * tests restCall
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Framework\Rest\ApiClient::restCall
     */
    public function testRestCall()
    {
        $params = [];
        $response = $this->apiClient->restCall($params);
        $response = json_decode($response, true);
        $this->assertIsArray($response);
    }

    /**
     * tests withSecurity
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Framework\Rest\ApiClient::withSecurity
     */
    public function testWithSecurity()
    {
        $username = '12345678';
        $password = 'xxxxxxxxxxxx';
        $this->assertIsObject($this->apiClient->withSecurity($username, $password));
    }

    /**
     * tests withLicenseKey
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Framework\Rest\ApiClient::withLicenseKey
     */
    public function testWithLicenseKey()
    {
        $accountId = '12345678';
        $licenseKey = 'xxxxxxxxxxxx';
        $this->assertIsObject($this->apiClient->withLicenseKey($accountId, $licenseKey));
    }

    /**
     * tests withBearerToken
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Framework\Rest\ApiClient::withBearerToken
     */
    public function testWithBearerToken()
    {
        $bearerToken = 'xyz123';
        $this->assertIsObject($this->apiClient->withBearerToken($bearerToken));
    }

    /**
     * tests withBasicToken
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Framework\Rest\ApiClient::withBasicToken
     */
    public function testWithBasicToken()
    {
        $accountId = '12345678';
        $licenseKey = 'xxxxxxxxxxxx';
        $this->assertIsObject($this->apiClient->withBasicToken($accountId, $licenseKey));
    }

    /**
     * tests withCatchExceptions
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Framework\Rest\ApiClient::withCatchExceptions
     */
    public function testWithCatchExceptions()
    {
        $this->assertIsObject($this->apiClient->withCatchExceptions());
    }

    /**
     * tests getClient
     * @test
     * @covers \ClassyLlama\AvaTax\BaseProvider\Framework\Rest\ApiClient::getClient
     */
    public function testGetClient()
    {
        $this->assertIsObject($this->apiClient->getClient());
    }

}