<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class BlocksList implements ResolverInterface
{
    /** @var BlockRepositoryInterface */
    private $blockRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param BlockRepositoryInterface $blockRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Authentication $authentication
    ) {
        $this->blockRepository = $blockRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->authentication = $authentication;
    }

    /**
     * Return All CMS Blocks for UI
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $blocksData = $this->getBlocksData($storeId);

        return [
            'items' => $blocksData,
        ];
    }

    /**
     * Get blocks data
     *
     * @param int $storeId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getBlocksData(int $storeId): array
    {
        $blocksData = [];
        $search = $this->searchCriteriaBuilder
            //->addFilter(BlockInterface::BLOCK_ID, [0], 'in')
            ->create();
        $blockList = $this->blockRepository->getList($search)->getItems();
        
        foreach ($blockList as $block) {
            $blocksData[] = [
                'block_id' => $block->getBlockId(),
                'title' => $block->getTitle(),
                'identifier' => $block->getIdentifier(),
                'store_view_code' => $block->getStoreCode()
            ];
        }
        return $blocksData;
    }
}
