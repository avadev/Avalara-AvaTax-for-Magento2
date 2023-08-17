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
namespace ClassyLlama\AvaTax\Model\Items\Processing;

use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use ClassyLlama\AvaTax\Helper\Config as HelperConfig;
use ClassyLlama\AvaTax\Model\ProductsSyncFactory;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\Collection as CrossBorderClassCollection;
use ClassyLlama\AvaTax\Model\ResourceModel\CrossBorderClass\CollectionFactory as CrossBorderClassCollectionFactory;
use ClassyLlama\AvaTax\Framework\Interaction\Rest as RestClient;
use ClassyLlama\AvaTax\Model\Items\Processing\BatchProcessing;
/**
 * Class HsCodeBatchProcessing
 *
 * @package ClassyLlama\AvaTax\Model\Items\Processing
 */
class HsCodeBatchProcessing extends AbstractProcessing
    implements ProcessingStrategyInterface
{
    const BATCH_COLLECTION_PAGE_SIZE = 100;

     /**
     * @var AvaTaxLogger
     */
    protected $avaTaxLogger;
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var HelperConfig
     */
    protected $helperConfig;

    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    protected $taxClassRepository;
    /**
     * @var ProductsSyncFactory
     */
    protected $productsSyncFactory;
    /**
     * @var CrossBorderClassCollectionFactory
     */
    protected $cbClassCollectionFactory;

    public $companyId;
    /**
     * @var Rest
     */
    protected $restClient;
    /**
     * @var BatchProcessing
     */
    protected $batchProcessing;
    /**
     * HsCodeBatchProcessing constructor.
     *
     * @param AvaTaxLogger $avaTaxLogger
     * @param CollectionFactory $productCollectionFactory
     * @param CategoryFactory $categoryFactory
     * @param HelperConfig $helperConfig
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository
     * @param ProductsSyncFactory $productsSyncFactory
     * @param CrossBorderClassCollectionFactory $cbClassCollectionFactory
     * @param RestClient $restClient
     * @param BatchProcessing $batchProcessing
     */
    public function __construct(
        AvaTaxLogger $avaTaxLogger,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        HelperConfig $helperConfig,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        ProductsSyncFactory $productsSyncFactory,
        CrossBorderClassCollectionFactory $cbClassCollectionFactory,
        RestClient $restClient,
        BatchProcessing $batchProcessing
    ) {
        $this->avaTaxLogger = $avaTaxLogger;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->helperConfig = $helperConfig;
        $this->taxClassRepository = $taxClassRepository;
        $this->productsSyncFactory = $productsSyncFactory;
        $this->cbClassCollectionFactory = $cbClassCollectionFactory;
        $this->restClient = $restClient;
        $this->batchProcessing = $batchProcessing;
        $this->companyId = $this->helperConfig->getCompanyId();
    }

    /**
     * Execute HsCode Batch Processing
     * 
     * @return $this
     */
    public function execute()
    {
        if($this->helperConfig->isProductSyncEnabled() && !empty($this->companyId))
        {
            $pendingHsCodeProductCodes = $this->getPendingHsCodeProductCollection();
            $pendingHsCodeProductCollections = $this->batchProcessing->getProductCollection($pendingHsCodeProductCodes);
            if($pendingHsCodeProductCollections && count($pendingHsCodeProductCollections)>0)
            {
                foreach($pendingHsCodeProductCollections as $product)
                {
                    $cross_border_type_id = '';
                    if($product->getCustomAttribute('avatax_cross_border_type'))
                    {
                        $cross_border_type_id = $product->getCustomAttribute('avatax_cross_border_type')->getValue();
                        if(!empty($cross_border_type_id))
                        {
                            $hsCodeAndCountryCodeData = $this->getHSCodesWithCountryCodes($cross_border_type_id);
                            $classificationsData = $this->getClassificationsData($hsCodeAndCountryCodeData);
                            if(is_array($classificationsData) && count($classificationsData)>0)
                            {
                                $avataxprodid = array_search( $product->getSku(), $pendingHsCodeProductCodes );
                                $this->syncClassificationDataToAvaTax($product, $avataxprodid, $classificationsData);
                            }
                        }                        
                    }
                }   
            }                 
        }
        return $this;
    }
    /**
     * Sync Classification Data To Avatax
     *
     * @param $product
     * @param $avataxprodid
     * @param $classificationsData
     * 
     * @return mixed
     */
    protected function syncClassificationDataToAvaTax($product, $avataxprodid, $classificationsData)
    {
        $payloadArr = [];
        $payloadArr['companyid'] = $this->companyId;
        $productArr = $this->batchProcessing->prepareItemCatalogue($product, $avataxprodid, $classificationsData);
        $payloadArr['body'][] = $productArr;
        $this->batchProcessing->addHsCodeSyncQueueProcess($payloadArr);
        return;
    }
    /**
     * Get Classification Data from Product Cross Border Class and AvaTax
     *
     * @param $hsCodeAndCountryCodeData
     * 
     * @return array
     */
    public function getClassificationsData($hsCodeAndCountryCodeData)
    {
        $classificationsData = [];
        if(is_array($hsCodeAndCountryCodeData) && count($hsCodeAndCountryCodeData)>0)
        {
            $i=0;
            foreach($hsCodeAndCountryCodeData as $data)
            {
                $countryCode = $data['country_id'];
                $client = $this->restClient->getClient();
                $client->withCatchExceptions(false);
                $productClassificationResult = $client->listProductClassificationSystems(null, null, null, null, $countryCode);
                $systemCode = $this->getSystemCodeFromProductClassification($productClassificationResult, $data['country_id']);
                $classificationsData[$i]['systemCode'] = $systemCode;
                $classificationsData[$i]['productCode'] = $data['hs_code'];
                $i++;
            }            
        }
        return $classificationsData;
    }
    /**
     * Get System Code from AvaTax Product Classification
     *
     * @param $productClassificationResult
     * @param $countryCode
     * 
     * @return mixed
     */
    protected function getSystemCodeFromProductClassification($productClassificationResult, $countryCode)
    {
        if(
            $productClassificationResult && 
            $productClassificationResult->value &&
            count($productClassificationResult->value) > 0
         )
        {
            $systemCode = '';
            foreach($productClassificationResult->value as $value)
            {
                if($value->systemCode != 'AVATAXCODE')
                {
                    if($value->countries && count($value->countries)>0)
                    {
                        $countryFound = false;
                        foreach($value->countries as $c)
                        {
                            if(!$countryFound && $c->country == $countryCode)
                            {
                                $systemCode = $value->systemCode;
                                $countryFound = true;
                            }                                
                        }
                    }
                }
            }
            return $systemCode;
        }
        return false;
    }
    /**
     * Get HSCode and Country Code from DB Table
     *
     * @param $cross_border_type_id
     * 
     * @return array
     */
    public function getHSCodesWithCountryCodes($cross_border_type_id)
    {
        $hsCodeAndCountryCodeData = [];
        $cbCollection = $this->getCrossBorderClassCollection($cross_border_type_id);
        if($cbCollection->getData() && count($cbCollection->getData())>0)
        {
            $hsCodeAndCountryCodeData = $cbCollection->getData();
        }
        return $hsCodeAndCountryCodeData;
    }
    /**
     * Get Cross Border Class Collection filter by cross border type id
     *
     * @param $cross_border_type_id
     * 
     * @return object
     */
    protected function getCrossBorderClassCollection($cross_border_type_id = '')
    {
        $cbCollection = $this->cbClassCollectionFactory->create()
                ->addFieldToSelect(['hs_code'])
                ->addFieldToFilter('cross_border_type_id', $cross_border_type_id)
                ->join(
                        ['country_links' => 'avatax_cross_border_class_country'],
                        'main_table.class_id = country_links.class_id',
                        ['country_id']
                    );
        return $cbCollection;
    }
    /**
     * Get Pending Sync HSCode Products Collection from DB 
     
     * @return array
     */
    protected function getPendingHsCodeProductCollection()
    {
        $productsSyncFactory = $this->productsSyncFactory->create();
        $productsSyncCollection = $productsSyncFactory->getCollection()
                                    ->addFieldToFilter('companyid', $this->companyId)
                                    ->addFieldToFilter('syncstatus', $this->helperConfig::AVATAX_PRODUCTS_SYNC_COMPLETED_CODE)
                                    ->addFieldToFilter(
                                        'hscodesyncstatus', 
                                        [
                                            [ 'null' => true ], 
                                            [ 'eq' => $this->helperConfig::AVATAX_PRODUCTS_HSCODE_SYNC_EMPTY_CODE ], 
                                            [ 'eq' => $this->helperConfig::AVATAX_PRODUCTS_HSCODE_SYNC_PENDING_CODE ]
                                        ]
                                    )
                                   ->setPageSize(self::BATCH_COLLECTION_PAGE_SIZE);
        $pendingHsCodeProductCodes = [];
        if(is_array($productsSyncCollection->getData()) && count($productsSyncCollection->getData())>0)
        {
            foreach($productsSyncCollection->getData() as $syncRecord)
            {
                $pendingHsCodeProductCodes[$syncRecord['avataxprodid']] = $syncRecord['itemcode'];
            }
        }
        return $pendingHsCodeProductCodes;
    }
}
