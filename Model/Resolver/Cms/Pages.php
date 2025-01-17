<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Cms\Api\Data\PageInterface;
use Magento\CmsGraphQl\Model\Resolver\DataProvider\Page as PageDataProvider;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use function is_numeric;

class Pages implements ResolverInterface
{
    /**  @var PageDataProvider */
    private $pageDataProvider;

    /** @var PageRepositoryInterface */
    private $pageRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     *
     * @param PageDataProvider $pageDataProvider
     * @param PageRepositoryInterface $pageRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @return void
     */
    public function __construct(
        PageDataProvider $pageDataProvider,
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->pageDataProvider = $pageDataProvider;
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        if ($args['identifiers'][0] == '') {
            $pagesData = $this->getAllPages($storeId);
        } else {
            $pageIdentifiers = $this->getPageIdentifiers($args);
            $pagesData = $this->getPagesData($pageIdentifiers, $storeId);
        }

        return [
            'items' => $pagesData,
        ];
    }

    /**
     * Get page identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getPageIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of CMS pages should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get pages data
     *
     * @param array $pageIdentifiers
     * @param int $storeId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getPagesData(array $pageIdentifiers, int $storeId): array
    {
        $pagesData = [];
        foreach ($pageIdentifiers as $pageIdentifier) {
            try {
                if (!is_numeric($pageIdentifier)) {
                    $pagesData[$pageIdentifier] = $this->pageDataProvider
                        ->getDataByPageIdentifier($pageIdentifier, $storeId);
                } else {
                    $pagesData[$pageIdentifier] = $this->pageDataProvider
                        ->getDataByPageId((int)$pageIdentifier, $storeId);
                }
            } catch (NoSuchEntityException $e) {
                $pagesData[$pageIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $pagesData;
    }

    /**
     * Get all pages data
     *
     * @param int $storeId
     * @return array
     */
    private function getAllPages($storeId)
    {
        $pagesData = [];
        $search = $this->searchCriteriaBuilder
            ->addFilter('store_id', [0,$storeId], 'in')
            ->create();
        $pageList = $this->pageRepository->getList($search)->getItems();
        
        foreach ($pageList as $page) {
            $l = $page->getId();
            $pagesData[$page->getId()] = $this->pageDataProvider
            ->getDataByPageId((int)$page->getId());
        }
        return $pagesData;
    }
}
