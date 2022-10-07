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

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Crossborder\Classes;

use Magento\Backend\App\Action\Context;
use ClassyLlama\AvaTax\Api\Data\CrossBorderClassRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * @codeCoverageIgnore
 */
class Save extends \ClassyLlama\AvaTax\Controller\Adminhtml\Crossborder\ClassesAbstract
{
    /**
     * @var CrossBorderClassRepositoryInterface
     */
    protected $crossBorderClassRepository;

    protected $dataPersistor;

    /**
     * @param Context $context
     * @param CrossBorderClassRepositoryInterface $crossBorderClassRepository
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        CrossBorderClassRepositoryInterface $crossBorderClassRepository,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->crossBorderClassRepository = $crossBorderClassRepository;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('class_id');

            if (empty($data['class_id'])) {
                $data['class_id'] = null;
            }

            /** @var \ClassyLlama\AvaTax\Api\Data\CrossBorderClassInterface $class */
            try {
                $class = ($id) ? $this->crossBorderClassRepository->getById($id) : $this->crossBorderClassRepository->create();
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('Cross Border Class does not exist'));
                return $resultRedirect->setPath('*/*');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('A problem occurred while trying to initialize Cross Border Class'));
                return $resultRedirect->setPath('*/*');
            }

            if (isset($data['cross_border_type_id'])) {
                $class->setCrossBorderTypeId($data['cross_border_type_id']);
            }
            if (isset($data['hs_code'])) {
                $class->setHsCode($data['hs_code']);
            }
            if (isset($data['unit_name'])) {
                $class->setUnitName($data['unit_name']);
            }
            if (isset($data['unit_amount_product_attr'])) {
                $class->setUnitAmountAttrCode($data['unit_amount_product_attr']);
            }
            if (isset($data['pref_program_indicator'])) {
                $class->setPrefProgramIndicator($data['pref_program_indicator']);
            }
            if (isset($data['destination_countries'])) {
                $countries = $data['destination_countries'];

                // If one of the selected country options is "Any", don't associate any specific countries
                if (in_array(\ClassyLlama\AvaTax\Model\Config\Source\CrossBorderClass\Countries::OPTION_VAL_ANY, $countries)) {
                    $class->setDestinationCountries([]);
                } else {
                    $class->setDestinationCountries($countries);
                }
            }

            try {
                $this->crossBorderClassRepository->save($class);
                $this->messageManager->addSuccessMessage(__('You saved the Cross Border Class'));
                $this->dataPersistor->clear('crossborder_class');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $class->getId()]);
                }
                return $resultRedirect->setPath('*/*');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Cross Border Class'));
            }

            $this->dataPersistor->set('crossborder_class', $data);

            if ($id) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            } else {
                return $resultRedirect->setPath('*/*/save');
            }
        }
        return $resultRedirect->setPath('*/*');
    }
}