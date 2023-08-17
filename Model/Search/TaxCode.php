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
namespace ClassyLlama\AvaTax\Model\Search;

/**
 * Tax Code Search Model
 *
 * @method TaxCode setQuery(string $query)
 * @method string|null getQuery()
 * @method bool hasQuery()
 * @method TaxCode setStart(int $startPosition)
 * @method int|null getStart()
 * @method bool hasStart()
 * @method TaxCode setLimit(int $limit)
 * @method int|null getLimit()
 * @method bool hasLimit()
 * @method TaxCode setResults(array $results)
 * @method array getResults()
 * @method TaxCode setParam(string $isShippingCode)
 * @method string|null getParam()
 */
class TaxCode extends \Magento\Framework\DataObject
{
    /**
     * @var \ClassyLlama\AvaTax\Api\TaxCodeRepositoryInterface
     */
    protected $taxCodeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Initialize dependencies.
     *
     * @param \ClassyLlama\AvaTax\Api\TaxCodeRepositoryInterface $taxCodeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \ClassyLlama\AvaTax\Api\TaxCodeRepositoryInterface $taxCodeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->taxCodeRepository = $taxCodeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $result = [];
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($result);
            return $this;
        }

        $this->searchCriteriaBuilder->setCurrentPage($this->getStart());
        $this->searchCriteriaBuilder->setPageSize($this->getLimit());

        $searchFields = ['tax_code', 'description'];
        foreach ($searchFields as $field) {
            $queryFilters[] = $this->filterBuilder
                ->setField($field)
                ->setConditionType('like')
                ->setValue('%'. $this->getQuery() . '%')
                ->create();
        }

        $activeFilter[] = $this->filterBuilder
            ->setField('is_active')
            ->setConditionType('eq')
            ->setValue(true)
            ->create();

        $this->searchCriteriaBuilder->addFilters($queryFilters)->addFilters($activeFilter);

        // Additional filter for shipping tax code
        if ($this->getParam()) {
            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder->setField('tax_code_type_id')
                        ->setConditionType('eq')
                        ->setValue('F')
                        ->create(),
                ]
            );
        }
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->taxCodeRepository->getList($searchCriteria);

        if ($searchResults->getTotalCount() > 0) {
            foreach ($searchResults->getItems() as $taxcode) {
                $result[] = [
                    'code' => $taxcode['tax_code'],
                    'description' => $taxcode['description']
                ];
            }
        }
        
        $this->setResults($result);
        return $this;
    }
}
