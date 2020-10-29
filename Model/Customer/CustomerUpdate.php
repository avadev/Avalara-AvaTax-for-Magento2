<?php declare(strict_types=1);

namespace ClassyLlama\AvaTax\Model\Customer;

use ClassyLlama\AvaTax\Api\CustomerUpdateInterface;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\DataObject as CustomerUpdateResults;
use Magento\Customer\Api\CustomerRepositoryInterface;
use ClassyLlama\AvaTax\Api\RestCustomerInterface as AvalaraCustomerUpdateService;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;

/**
 * Class CustomerUpdate
 * @package ClassyLlama\AvaTax\Model\Customer
 */
class CustomerUpdate implements CustomerUpdateInterface
{
    
    /**
     * @var AvaTaxLogger
     */
    protected $logger;

    /**
     * @var CustomerUpdateResults
     */
    public $customerUpdateResults;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AvalaraCustomerUpdateService
     */
    protected $avalaraCustomerUpdateService;

    /**
     * CustomerUpdate constructor.
     * @param AvaTaxLogger $logger
     * @param CustomerUpdateResults $customerUpdateResults
     * @param CustomerRepositoryInterface $customerRepository
     * @param AvalaraCustomerUpdateService $avalaraCustomerUpdateService
     */
    public function __construct(
        AvaTaxLogger $logger,
        CustomerUpdateResults $customerUpdateResults,
        CustomerRepositoryInterface $customerRepository,
        AvalaraCustomerUpdateService $avalaraCustomerUpdateService
    ) {
        $this->logger = $logger;
        $this->customerUpdateResults = $customerUpdateResults;
        $this->customerRepository = $customerRepository;
        $this->avalaraCustomerUpdateService = $avalaraCustomerUpdateService;
        $this->customerUpdateResults->setData('result', []);
    }

    /**
     * Update customer information at the Avalara service
     *
     * @param int|null $customerId
     * @return CustomerUpdateInterface
     */
    public function updateCustomerInformation(int $customerId = null): CustomerUpdateInterface
    {
        try {
            if (null !== $customerId) {
                /** @var CustomerInterface $customer */
                $customer = $this->customerRepository->getById($customerId);
                /** @var DataObject $response */
                $response = $this->avalaraCustomerUpdateService->updateCustomer($customer, null, (int)$customer->getStoreId());
                if ($response instanceof DataObject && !empty($response->getCustomerCode())) {
                    $this->prepareAvalaraServiceResponse(false, "success");
                } else {
                    $this->prepareAvalaraServiceResponse(true, "error");
                }
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), [
                'class' => self::class,
                'trace' => $exception->getTraceAsString()
            ]);
            $this->prepareAvalaraServiceResponse(
                true,
                "Error happened while trying to update Customer information on Avalara service, for more information please check the log."
            );
        }
        return $this;
    }

    /**
     * @param bool $errorStatus
     * @param string $message
     * @return CustomerUpdateInterface
     */
    private function prepareAvalaraServiceResponse(bool $errorStatus = true, string $message = ''): CustomerUpdateInterface
    {
        $this->customerUpdateResults->setData([
            'result' => [
                'error_status' => $errorStatus,
                'message' => $message
            ]
        ]);
        return $this;
    }
}
