<?php
declare(strict_types=1);

namespace ClassyLlama\AvaTax\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\App\Config\ScopeConfigInterface; 
use Magento\Shipping\Model\Config;
/**
 * @codeCoverageIgnore
 */
class TransportShippingColumn extends Select
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig; 
    /**
     * @var Config
     */
    protected $shippingModelconfig;
    /**
     * @param Context $context
     * @param Config $shippingModelconfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
                \Magento\Framework\View\Element\Template\Context $context,
                Config $shippingModelconfig, 
                ScopeConfigInterface $scopeConfig,
                array $data = []
            )
    { 
        parent::__construct($context, $data);
        $this->shippingModelconfig = $shippingModelconfig;
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        $shippings = $this->shippingModelconfig->getActiveCarriers();
        $methods = array();
        foreach($shippings as $shippingCode => $shippingModel)
        {
            $carrierMethods = $shippingModel->getAllowedMethods();
            if($carrierMethods)
            {
                foreach ($carrierMethods as $methodCode => $method)
                {
                    $code = $shippingCode.'_'.$methodCode;
                    $carrierTitle = $this->scopeConfig->getValue('carriers/'. $shippingCode.'/title');
                    $methods[] = array('value'=>$code,'label'=>$carrierTitle);
                }
            }
        }
        return $methods;        
    }
}