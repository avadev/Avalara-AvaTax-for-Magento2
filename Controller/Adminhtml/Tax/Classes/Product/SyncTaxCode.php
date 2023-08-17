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

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes\Product;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes;
use ClassyLlama\AvaTax\Model\TaxCodeSync;
use ClassyLlama\AvaTax\Helper\Config;

/**
 * Class SyncTaxCode
 * @package ClassyLlama\AvaTax\Controller\Adminhtml\Tax\Classes\Product
 */

/**
 * @codeCoverageIgnore
 */
class SyncTaxCode extends Classes
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     *
     * @var TaxCodeSync
     */
    protected $taxCodeSync;

    /**
     * SyncTaxCode constructor
     *
     * @param Context $context
     * @param TaxCodeSync $taxCodeSync
     * @param Config $config
     */
    public function __construct(
        Context $context,
        TaxCodeSync $taxCodeSync,
        Config $config
    ) {
        $this->taxCodeSync = $taxCodeSync;
        $this->config = $config;
        
        parent::__construct($context);
    }

    /**
     * @return Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $error = 0;

        try {
            // Get company ids for all scope at store and website level
            $companyIds = $this->taxCodeSync->getConfigCompanies();

            if (!empty($companyIds) && count($companyIds) > 0) {
                foreach($companyIds as $key => $company) {
                    if ($key == 0) {
                        $fetchGlobalTax = true;
                    } else {
                        $fetchGlobalTax = false;
                    }
                    // Sync product tax codes from Avatax
                    $resultObj[] = $this->taxCodeSync->synchTaxCodes(
                        $company['value'], $company['isProd'], $company['scope_id'], $company['scope'], $fetchGlobalTax
                    );
                }
            }

            if ($resultObj && array_sum($resultObj) > 0) {
                $message = __(
                    '#%1 records successfully synced from AvaTax.',
                    array_sum($resultObj)
                );
            } else {
                $message = __(
                    'All records already synced from AvaTax.'
                );
            }
            $this->messageManager->addSuccessMessage($message);
        } catch (\Exception $e) {
            $error = 1;
            $message = __(
                'Error encountered in tax code sync, '.$e->getMessage()
            );
        }

        if ($error == 1) {
            $this->messageManager->addErrorMessage($message);
        }

        // Redirect browser to product tax classes page
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('avatax/tax_classes_product/index');
        return $resultRedirect;
    }
}
