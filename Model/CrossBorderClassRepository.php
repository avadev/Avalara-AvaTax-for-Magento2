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
use ClassyLlama\AvaTax\Model\Data\CrossBorderClassFactory as CrossBorderClassDataFactory;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @var CrossBorderClassDataFactory
     */
    protected $crossBorderClassDataFactory;

    /**
     * @var CrossBorderClassResource
     */
    protected $crossBorderClassResource;

    /**
     * @param CrossBorderClassFactory $crossBorderClassFactory
     * @param CrossBorderClassResourceFactory $crossBorderClassResourceFactory
     * @param CrossBorderClassDataFactory $crossBorderClassDataFactory
     */
    public function __construct(
        CrossBorderClassFactory $crossBorderClassFactory,
        CrossBorderClassResourceFactory $crossBorderClassResourceFactory,
        CrossBorderClassDataFactory $crossBorderClassDataFactory
    ) {
        $this->crossBorderClassFactory = $crossBorderClassFactory;
        $this->crossBorderClassResourceFactory = $crossBorderClassResourceFactory;
        $this->crossBorderClassDataFactory = $crossBorderClassDataFactory;

        $this->crossBorderClassResource = $this->crossBorderClassResourceFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getById($classId)
    {
        $crossBorderClass = $this->loadModel($classId);

        return $crossBorderClass->getDataModel();
    }

    /**
     * @inheritdoc
     */
    public function create()
    {
        return $this->crossBorderClassDataFactory->create();
    }

    /**
     * @inheritdoc
     *
     */
    public function save($classDataModel)
    {
        $classResource = $this->crossBorderClassResourceFactory->create();

        $classData = [
            'cross_border_type' => $classDataModel->getCrossBorderType(),
            'hs_code' => $classDataModel->getHsCode(),
            'unit_name' => $classDataModel->getUnitName(),
            'unit_amount_product_attr' => $classDataModel->getUnitAmountAttrCode(),
            'pref_program_indicator' => $classDataModel->getPrefProgramIndicator(),
        ];

        $class = $this->crossBorderClassFactory->create();
        if ($classDataModel->getId()) {
            $classResource->load($class, $classDataModel->getId());
            $classData['class_id'] = $classDataModel->getId();
        }

        $class->setData($classData);

        $classResource->save($class);

        return $this->getById($class->getId());
    }

    /**
     * @inheritdoc
     */
    public function deleteById($classId)
    {
        $crossBorderClass = $this->loadModel($classId);

        $this->crossBorderClassResource->delete($crossBorderClass);
    }

    /**
     * Load the full model for a class
     *
     * @param int $classId
     * @return CrossBorderClass
     * @throws NoSuchEntityException
     */
    protected function loadModel($classId)
    {
        /**
         * @var CrossBorderClass $crossBorderClass
         */
        $crossBorderClass = $this->crossBorderClassFactory->create();

        $this->crossBorderClassResource->load($crossBorderClass, $classId);

        if (!$crossBorderClass->getClassId()) {
            throw new NoSuchEntityException(__('Cross-border Class w/ ID %1 does not exist', $classId));
        }

        return $crossBorderClass;
    }
}