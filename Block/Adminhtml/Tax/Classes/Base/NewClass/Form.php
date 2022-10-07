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
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Block\Adminhtml\Tax\Classes\Base\NewClass;

/**
 * Create form
 */
/**
 * @codeCoverageIgnore
 */
abstract class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Tax class type
     *
     * @var null|string
     */
    protected $classType = null;

    /**
     * AvaTax CustomerUsageType
     *
     * @var \ClassyLlama\AvaTax\Model\Config\Source\AvaTaxCustomerUsageType|null
     */
    protected $avaTaxCustomerUsageType = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     * @param \ClassyLlama\AvaTax\Model\Config\Source\AvaTaxCustomerUsageType $avaTaxCustomerUsageType
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data,
        \ClassyLlama\AvaTax\Model\Config\Source\AvaTaxCustomerUsageType $avaTaxCustomerUsageType
    ) {
        $this->avaTaxCustomerUsageType = $avaTaxCustomerUsageType;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('avatax_tax_classes_' .  \strtolower($this->classType));
    }

    /**
     * Prepare form fields and structure
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_tax_class');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Tax Class')]);

        $fieldset->addField('is_new', 'hidden', ['name' => 'is_new', 'value' => 1]);

        $fieldset->addField(
            'class_name',
            'text',
            [
                'name' => 'class_name',
                'label' => __('Class Name'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $this->addAvaTaxCodeField($fieldset);

        if ($model) {
            $form->addValues($model->getData());
        }
        $form->setAction($this->getUrl('avatax/tax_classes_' .  \strtolower($this->classType) . '/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Add field for AvaTax Code
     *
     * Since the AvaTax Code options will be different based on customer vs product, each implementation of this class
     * must add field
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return $this
     */
    abstract function addAvaTaxCodeField(\Magento\Framework\Data\Form\Element\Fieldset $fieldset);
}
