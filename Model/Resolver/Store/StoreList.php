<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Store;

use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class StoreList implements ResolverInterface
{
    /** @var StoreRepositoryInterface */
    private $storeRepository;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /** @var Authentication */
    private $authentication;

    /**
     * @param StoreRepositoryInterface $storeRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param Authentication $authentication
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        GroupRepositoryInterface $groupRepository,
        WebsiteRepositoryInterface $websiteRepository,
        Authentication $authentication
    ) {
        $this->storeRepository = $storeRepository;
        $this->groupRepository = $groupRepository;
        $this->websiteRepository = $websiteRepository;
        $this->authentication = $authentication;
    }

    /**
     * Return All Stores for UI
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

        $storeData = $this->getStoreData();

        return [
            'items' => $storeData
        ];
    }

    /**
     * Get all store data
     *
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getStoreData(): array
    {
        $storeData = [];

        $storeList = $this->storeRepository->getList();
        
        foreach ($storeList as $store) {
            if ($store->getCode() != 'admin') {
                $storeData[] = [
                'store_code' => $store->getCode(),
                'store_name' => $store->getName(),
                'group_code' => $this->groupRepository->get($store->getStoreGroupId())->getCode(),
                'group_name' => $this->groupRepository->get($store->getStoreGroupId())->getName(),
                'website_code' => $this->websiteRepository->get($store->getWebsiteId())->getCode(),
                'website_name' => $this->websiteRepository->get($store->getWebsiteId())->getName()
                ];
            }
        }
        return $storeData;
    }
}
