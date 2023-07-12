<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerGroup
{
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteria;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteria
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteria
    ) {
        $this->groupRepository = $groupRepository;
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * Get customer group by name
     *
     * @param string $groupName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getGroupDataByName(string $groupName): array
    {
        $groupData = $this->fetchGroupData($groupName, GroupInterface::CODE);

        return $groupData;
    }

    /**
     * Get group data by id
     *
     * @param int $groupId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getGroupDataById(int $groupId): array
    {
        $groupData = $this->fetchGroupData($groupId, GroupInterface::ID);

        return $groupData;
    }

    /**
     * Fetch group data by either id or field
     *
     * @param mixed $identifier
     * @param string $field
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchGroupData($identifier, string $field): array
    {
        if ($field==GroupInterface::ID) {
            $group = $this->groupRepository->getById($identifier);
        } else {
            $search = $this->searchCriteria
            ->addFilter($field, $identifier, 'eq')->create()->setPageSize(1)
            ->setCurrentPage(1);
            $groupList = $this->groupRepository->getList($search);
            $group = current($groupList->getItems());
        }
        if (empty($group)) {
            throw new NoSuchEntityException(
                __('The group with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }

        return [
            'name' => $group->getCode(),
            'tax_class' => $group->getTaxClassName()
        ];
    }
}
