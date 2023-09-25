<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use MagentoEse\DataInstallGraphQl\Model\Resolver\Export\NegotiableQuoteExport;

class NegotiableQuotes
{
    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private NegotiableQuoteRepositoryInterface $negotiableQuoteRepository;
    /**
     * @var FilterBuilder
     */
    private FilterBuilder $filterBuilder;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var FilterGroupBuilder
     */
    private FilterGroupBuilder $filterGroupBuilder;
    /**
     * @var SortOrderBuilder
     */
    private SortOrderBuilder $sortOrderBuilder;
    /**
     * @var NegotiableQuoteExport
     */
    private NegotiableQuoteExport $exportQuotes;
    /**
     * @var JoinProcessorInterface
     */
    private JoinProcessorInterface $extensionAttributesJoinProcessor;
    /**
     * @var SearchResultsFactory
     */
    private SearchResultsFactory $searchResultsFactory;
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param NegotiableQuoteExport $exportQuotes
     * @param NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchResultsFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        NegotiableQuoteExport $exportQuotes,
        NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->exportQuotes = $exportQuotes;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Get all negotiable quotes based on filters
     *
     * @param array $identifiers
     * @param array $filterArgs
     * @param int $currentPage
     * @param int $pageSize
     * @param array $sortArgs
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getNegotiableQuotes(
        array $identifiers,
        array $filterArgs = [],
        int $currentPage = 1,
        int $pageSize = 1,
        array $sortArgs = []
    ): array {
        $this->searchCriteriaBuilder->addFilter('quote_id', $identifiers, 'in');
        $this->searchCriteriaBuilder->setCurrentPage($currentPage);
        $this->searchCriteriaBuilder->setPageSize($pageSize);
        $quotesCollection = $this->getList($this->searchCriteriaBuilder->create());
        /** @var \Magento\Framework\Api\SearchResultsInterface $quotesCollection */
        $quotes = $this->exportQuotes->generateData($quotesCollection->getItems(), 1);
        return $quotes;
    }

    /**
     * Get Negotiable Quotes List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): \Magento\Framework\Api\SearchResults
    {
        /** @var \Magento\Framework\Api\SearchResults $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);
        $collection->addFieldToFilter(
            'extension_attribute_negotiable_quote.is_regular_quote',
            ['eq' => 1]
        );

        $this->collectionProcessor->process($searchCriteria, $collection);

        $items = $collection->getItems();
        $searchResult->setItems($items);
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}
