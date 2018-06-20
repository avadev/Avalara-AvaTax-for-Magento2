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

namespace ClassyLlama\AvaTax\Tests\Unit\Model;

use ClassyLlama\AvaTax\Model\CrossBorderClass;
use ClassyLlama\AvaTax\Model\Data\CrossBorderClass as CrossBorderClassData;

class CrossBorderClassRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \ClassyLlama\AvaTax\Model\CrossBorderClassFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $crossBorderClassFactory;

    /**
     * @var \ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClassFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $crossBorderClassResourceFactory;

    /**
     * @var \ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $crossBorderClassResource;

    /**
     * @var \ClassyLlama\AvaTax\Model\CrossBorderClassRepository
     */
    protected $crossBorderClassRepository;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->crossBorderClassFactory = $this->createPartialMock(
            \ClassyLlama\AvaTax\Model\CrossBorderClassFactory::class,
            ['create']
        );

        $this->crossBorderClassResourceFactory = $this->createPartialMock(
            \ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClassFactory::class,
            ['create']
        );

        $this->crossBorderClassResource = $this->createPartialMock(
            \ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass::class,
            ['load']
        );
        $this->crossBorderClassResourceFactory->method('create')->willReturn($this->crossBorderClassResource);

        $this->crossBorderClassRepository = $this->objectManager->getObject(
            \ClassyLlama\AvaTax\Model\CrossBorderClassRepository::class,
            [
                'crossBorderClassFactory' => $this->crossBorderClassFactory,
                'crossBorderClassResourceFactory' => $this->crossBorderClassResourceFactory,
            ]
        );
    }

    public function testGetById()
    {
        // Arrange
        $id = 1;
        $countries = ['US', 'DE'];
        $crossBorderType = 'type1';
        $hsCode = '12345';
        $unitName = 'kg';
        $unitAmountProductAttr = 'weight_kg';
        $prefProgramIndicator = 'NAFTA';

        $crossBorderClassDataModel = $this->createPartialMock(CrossBorderClassData::class, []);

        $crossBorderClass = $this->createPartialMock(
            CrossBorderClass::class,
            ['createDataModel', 'getClassId', 'getDestinationCountryCodes', 'getCrossBorderType',
                'getHsCode', 'getUnitName', 'getUnitAmountProductAttr', 'getPrefProgramIndicator']
        );
        $crossBorderClass->method('createDataModel')->willReturn($crossBorderClassDataModel);
        $crossBorderClass->method('getClassId')->willReturn($id);
        $crossBorderClass->method('getDestinationCountryCodes')->willReturn($countries);
        $crossBorderClass->method('getCrossBorderType')->willReturn($crossBorderType);
        $crossBorderClass->method('getHsCode')->willReturn($hsCode);
        $crossBorderClass->method('getUnitName')->willReturn($unitName);
        $crossBorderClass->method('getUnitAmountProductAttr')->willReturn($unitAmountProductAttr);
        $crossBorderClass->method('getPrefProgramIndicator')->willReturn($prefProgramIndicator);

        $this->crossBorderClassFactory->method('create')->willReturn($crossBorderClass);


        // Act and Assert
        $this->crossBorderClassResource->expects($this->once())->method('load');

        $result = $this->crossBorderClassRepository->getById($id);

        $this->assertInstanceOf(CrossBorderClassData::class, $result, 'CrossBorderClass data model not returned by repo');

        $this->assertEquals($countries, $result->getDestinationCountries(), "CrossBorderClass destination countries don't match");
        $this->assertEquals($crossBorderType, $result->getCrossBorderType(), "CrossBorderClass cross border type doesn't match");
        $this->assertEquals($hsCode, $result->getHsCode(), "CrossBorderClass HS code doesn't match");
        $this->assertEquals($unitName, $result->getUnitName(), "CrossBorderClass unit name doesn't match");
        $this->assertEquals($unitAmountProductAttr, $result->getUnitAmountAttrCode(), "CrossBorderClass unit amount attribute doesn't match");
        $this->assertEquals($prefProgramIndicator, $result->getPrefProgramIndicator(), "CrossBorderClass pref program indicator doesn't match");
    }
}