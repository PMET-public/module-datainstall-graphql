<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\Exception\LocalizedException;
use Magento\SharedCatalog\Api\CompanyManagementInterface;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class PublicSharedCatalog implements ResolverInterface
{
    /** @var SharedCatalogRepositoryInterface */
    private $sharedCatalogRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var CompanyManagementInterface */
    private $companyManagementInterface;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CompanyManagementInterface $companyManagementInterface
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        SharedCatalogRepositoryInterface $sharedCatalogRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CompanyManagementInterface $companyManagementInterface,
        Authentication $authentication
    ) {
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->companyManagementInterface = $companyManagementInterface;
        $this->authentication = $authentication;
    }
    
    /**
     * Get Public Shared Catalog
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws LocalizedException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

        $search = $this->searchCriteriaBuilder
        ->addFilter(SharedCatalogInterface::TYPE, SharedCatalogInterface::TYPE_PUBLIC, 'eq')->create();
        $catalogList = $this->sharedCatalogRepository->getList($search)->getItems();
        $catalog = current($catalogList);
                return [
                    'id' => $catalog->getId(),
                    'name' => $catalog->getName(),
                    'description' => $catalog->getDescription(),
                    'type' => ($catalog->getType()==0) ? 'Custom' : 'Public'
                ];
    }
}
