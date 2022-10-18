<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockExtension;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

class MsiStock
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var GetStockSourceLinksInterface
     */
    private $stockSourceLink;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteria;

    /**
     * @param StockRepositoryInterface $stockRepository
     * @param GetStockSourceLinksInterface $stockSourceLink
     * @param SearchCriteriaBuilder $searchCriteria
     */
    public function __construct(
        StockRepositoryInterface $stockRepository,
        GetStockSourceLinksInterface $stockSourceLink,
        SearchCriteriaBuilder $searchCriteria
    ) {
        $this->stockRepository = $stockRepository;
        $this->stockSourceLink = $stockSourceLink;
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * Get customer stock by name
     *
     * @param string $stockName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getStockDataByName(string $stockName): array
    {
        $stockData = $this->fetchStockData($stockName, StockInterface::NAME);

        return $stockData;
    }

    /**
     * Get stock data by id
     *
     * @param int $stockId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getStockDataById(int $stockId): array
    {
        $stockData = $this->fetchStockData($stockId, StockInterface::STOCK_ID);

        return $stockData;
    }

    /**
     * Fetch stock data by either id or field
     *
     * @param mixed $identifier
     * @param string $field
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchStockData($identifier, string $field): array
    {
        if ($field==StockInterface::STOCK_ID) {
            $stock = $this->stockRepository->get($identifier);
        } else {
            $search = $this->searchCriteria
            ->addFilter($field, $identifier, 'eq')->create()->setPageSize(1)
            ->setCurrentPage(1);
            $stockList = $this->stockRepository->getList($search);
            $stock = current($stockList->getItems());
        }
        if (empty($stock)) {
            throw new NoSuchEntityException(
                __('The stock with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }
        /** @var StockExtension $extensionAttributes */
        $extensionAttributes = $stock->getExtensionAttributes();
        
        /** @var StockInterface $stock */
        return [
            'stock_name' => $stock->getName(),
            'site_code' => $this->getWebsiteCodes($extensionAttributes->getSalesChannels()),
            'source_code' => $this->getStockSource($stock->getStockId())
        ];
    }

     /**
      * Get stock source code by id
      *
      * @param int $stockId
      * @return array
      * @throws NoSuchEntityException
      */
    private function getStockSource($stockId)
    {
        $search = $this->searchCriteria
        ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId, 'eq')->create();
        $sourceList = $this->stockSourceLink->execute($search)->getItems();
        //$group = current($groupList->getItems());
        $sourceCodes = [];
        foreach ($sourceList as $source) {
            $sourceCodes[] = $source->getSourceCode();
        }
        return implode(",", $sourceCodes);
    }

    /**
     * Get website codes in sales channel
     *
     * @param mixed $salesChannels
     * @return array
     * @throws NoSuchEntityException
     */
    private function getWebsiteCodes($salesChannels)
    {
        $siteCodes = [];
        foreach ($salesChannels as $salesChannel) {
            if ($salesChannel->getType()=='website') {
                 $siteCodes[] = $salesChannel->getCode();
            }
        }
        return implode(",", $siteCodes);
    }
}
