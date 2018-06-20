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

namespace ClassyLlama\AvaTax\Model;

use ClassyLlama\AvaTax\Model\CrossBorderClassFactory;
use ClassyLlama\AvaTax\Model\CrossBorderClass;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClassFactory as CrossBorderClassResourceFactory;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass as CrossBorderClassResource;

class CrossBorderClassRepository implements \ClassyLlama\AvaTax\Api\Data\CrossBorderClassRepositoryInterface
{
    /**
     * @var CrossBorderClassFactory
     */
    protected $crossBorderClassFactory;

    /**
     * @var CrossBorderClassResourceFactory
     */
    protected $crossBorderClassResourceFactory;

    /**
     * @param CrossBorderClassFactory $crossBorderClassFactory
     * @param CrossBorderClassResourceFactory $crossBorderClassResourceFactory
     */
    public function __construct(
        CrossBorderClassFactory $crossBorderClassFactory,
        CrossBorderClassResourceFactory $crossBorderClassResourceFactory
    ) {
        $this->crossBorderClassFactory = $crossBorderClassFactory;
        $this->crossBorderClassResourceFactory = $crossBorderClassResourceFactory;
    }

    /**
     * @inheritdoc
     */
    public function getById($classId)
    {
        /**
         * @var CrossBorderClass $crossBorderClass
         */
        $crossBorderClass = $this->crossBorderClassFactory->create();

        /**
         * @var CrossBorderClassResource $crossBorderClassResource
         */
        $crossBorderClassResource = $this->crossBorderClassResourceFactory->create();

        $crossBorderClassResource->load($crossBorderClass, $classId);

        return $crossBorderClass->getDataModel();
    }
}