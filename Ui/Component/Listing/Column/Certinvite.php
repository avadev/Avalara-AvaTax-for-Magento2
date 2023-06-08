<?php
namespace ClassyLlama\AvaTax\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Data\Form\FormKey;
use ClassyLlama\AvaTax\Helper\DocumentManagementConfig;

class Certinvite extends Column
{
    /**
     * @var DocumentManagementConfig
     */
    protected $documentManagementConfig; 
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    private $formKey;
    /**
     * Constructor
     *
     * @param ContextInterface                              $context
     * @param UiComponentFactory                            $uiComponentFactory
     * @param UrlInterface                                  $urlBuilder
     * @param DocumentManagementConfig                      $documentManagementConfig
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        FormKey $formKey,
        DocumentManagementConfig $documentManagementConfig,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->formKey = $formKey;
        $this->documentManagementConfig = $documentManagementConfig;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        
        if (isset($dataSource['data']['items'])) {
           foreach ($dataSource['data']['items'] as & $item) {
            $fieldName = $this->getData('name');
            $url = $this->urlBuilder->getUrl('avatax/invite/index',['form_key' => $this->formKey->getFormKey(), 'customer_id' => $item['entity_id'], 'redirect' => "1"]);
            $confirmMsg = "return confirm('This customer will be synced to AvaTax (using the customer\'s email and default billing address) and AvaTax will send an email to the customer, asking them to add an exemption certificate in the AvaTax interface. Would you like to proceed?', 'title hre')";
            $item[$fieldName] = '<a href="'.$url.'" onclick="'.$confirmMsg.'"><button class="button">Invite</button></a>';
            
           }
        }
        return $dataSource;
    }

    public function prepare()
    {
        // if certCapture is disabled hide certExpress column 
        $certCaptureEnable = $this->documentManagementConfig->isEnabled();
        if (!$certCaptureEnable) {
            $this->_data['config']['componentDisabled'] = true; // for removing the column
        }
        parent::prepare();
    }
}