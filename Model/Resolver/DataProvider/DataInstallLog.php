<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use MagentoEse\DataInstall\Api\LoggerRepositoryInterface;
use MagentoEse\DataInstall\Api\Data\LoggerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Data Installer Log data provider
 */
class DataInstallLog
{
    /**
     * @var LoggerRepositoryInterface
     */
    private $loggerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param LoggerRepositoryInterface $loggerRepository
     * @param FilterEmulate $widgetFilter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        LoggerRepositoryInterface $loggerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->loggerRepository = $loggerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get block data by identifier
     *
     * @param string $blockIdentifier
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getBlockByIdentifier(string $blockIdentifier, int $storeId): array
    {
        $blockData = $this->fetchBlockData($blockIdentifier, LoggerInterface::JOBID, $storeId);

        return $blockData;
    }

    /**
     * Get block data by block_id
     *
     * @param int $blockId
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getBlockById(int $blockId, int $storeId): array
    {
        $blockData = $this->fetchBlockData($blockId, BlockInterface::BLOCK_ID, $storeId);

        return $blockData;
    }

    /**
     * Fetch black data by either id or identifier field
     *
     * @param mixed $identifier
     * @param string $field
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchBlockData($identifier, string $field, int $storeId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter($field, $identifier)
            ->addFilter(Store::STORE_ID, [$storeId, Store::DEFAULT_STORE_ID], 'in')
            ->addFilter(BlockInterface::IS_ACTIVE, true)->create();

        $blockResults = $this->blockRepository->getList($searchCriteria)->getItems();

        if (empty($blockResults)) {
            throw new NoSuchEntityException(
                __('The CMS block with the "%1" ID doesn\'t exist.', $identifier)
            );
        }

        $block = current($blockResults);
        $renderedContent = $this->widgetFilter->filterDirective($block->getContent());
        return [
            BlockInterface::BLOCK_ID => $block->getId(),
            BlockInterface::IDENTIFIER => $block->getIdentifier(),
            BlockInterface::TITLE => $block->getTitle(),
            BlockInterface::CONTENT => $renderedContent,
        ];
    }
}
