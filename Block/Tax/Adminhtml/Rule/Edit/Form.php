<?php 

namespace ClassyLlama\AvaTax\Block\Tax\Adminhtml\Rule\Edit;

/**
 * Class Form
 */
class Form extends \Magento\Tax\Block\Adminhtml\Rule\Edit\Form
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Form constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Tax\Model\Rate\Source $rateSource
     * @param \Magento\Tax\Api\TaxRuleRepositoryInterface $ruleService
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassService
     * @param \Magento\Tax\Model\TaxClass\Source\Customer $customerTaxClassSource
     * @param \Magento\Tax\Model\TaxClass\Source\Product $productTaxClassSource
     * @param array $data
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Tax\Model\Rate\Source $rateSource,
        \Magento\Tax\Api\TaxRuleRepositoryInterface $ruleService,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassService,
        \Magento\Tax\Model\TaxClass\Source\Customer $customerTaxClassSource,
        \Magento\Tax\Model\TaxClass\Source\Product $productTaxClassSource,
        array $data,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $rateSource,
            $ruleService,
            $taxClassService,
            $customerTaxClassSource,
            $productTaxClassSource,
            $data
        );
    }

    /**
     * Add note to fields informing admin how to manage tax classes
     *
     * @return $this
     */
    public function _prepareForm()
    {
        $return = parent::_prepareForm();
        $fieldset = $this->getForm()->getElement('base_fieldset')->getContainer();
        $fieldset->getElement('tax_customer_class')->setNote(
            __(
                'Go to <a href="%1">Customer Tax Classes</a> to add AvaTax Customer Usage Types to tax classes.',
                $this->urlBuilder->getUrl('avatax/tax_classes_customer')
            )
        );
        $fieldset->getElement('tax_product_class')->setNote(
            __(
                'Go to <a href="%1">Product Tax Classes</a> to add AvaTax Tax Codes to tax classes.',
                $this->urlBuilder->getUrl('avatax/tax_classes_product')
            )
        );
        return $return;
    }
}
