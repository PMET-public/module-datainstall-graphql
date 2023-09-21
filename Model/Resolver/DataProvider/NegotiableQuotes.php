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
    private NegotiableQuoteRepositoryInterface $negotiableQuoteRepository;
    private FilterBuilder $filterBuilder;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private FilterGroupBuilder $filterGroupBuilder;
    private SortOrderBuilder $sortOrderBuilder;
    private NegotiableQuoteExport $exportQuotes;
    private JoinProcessorInterface $extensionAttributesJoinProcessor;
    private SearchResultsFactory $searchResultsFactory;
    private CollectionFactory $collectionFactory;
    private CollectionProcessorInterface $collectionProcessor;

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

    public function getNegotiableQuotes(
        array $identifiers,
        array $filterArgs = [],
        int $currentPage = 1,
        int $pageSize = 1,
        array $sortArgs = []
    ): array
    {
        /*if (!empty($filterArgs)) {
            $filterGroups = $this->createFilterGroups($filterArgs);
            $this->searchCriteriaBuilder->setFilterGroups($filterGroups);
        }

        if (!empty($sortArgs)) {

        }*/
        $this->searchCriteriaBuilder->addFilter('quote_id', $identifiers, 'in');
        $this->searchCriteriaBuilder->setCurrentPage($currentPage);
        $this->searchCriteriaBuilder->setPageSize($pageSize);
        $quotesCollection = $this->getList($this->searchCriteriaBuilder->create());
        /** @var \Magento\Framework\Api\SearchResultsInterface $quotesCollection */
        $quotes = $this->exportQuotes->generateData($quotesCollection->getItems(), 1);
        return $quotes;
    }

    /**
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
