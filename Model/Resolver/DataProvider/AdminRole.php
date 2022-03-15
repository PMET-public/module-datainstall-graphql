<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollection;
use Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory as RuleCollection;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer Role data provider
 */
class AdminRole
{
    /**
     * @var RoleCollection
     */
    private $roleCollection;

    /**
     * @var RuleCollection
     */
    private $ruleCollection;

    /**
     * @param RoleCollection $roleCollection
     * @param RuleCollection $ruleCollection
     */
    public function __construct(
        RoleCollection $roleCollection,
        RuleCollection $ruleCollection
    ) {
        $this->roleCollection = $roleCollection;
        $this->ruleCollection = $ruleCollection;
    }

    /**
     * Get customer role by name
     *
     * @param string $roleName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getRoleDataByName(string $roleName): array
    {
        $roleData = $this->fetchRoleData($roleName, 'role_name');

        return $roleData;
    }

    /**
     * Get customer role by id
     *
     * @param string $roleId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getRoleDataById(int $roleId): array
    {
        $roleData = $this->fetchRoleData($roleId, 'role_id');

        return $roleData;
    }

    /**
     * Fetch role data by field
     *
     * @param mixed $identifier
     * @param string $field
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchRoleData($identifier, string $field): array
    {
        $roleResults = $this->roleCollection->create()->addFieldToFilter($field, [$identifier])->getItems();
        $role = current($roleResults);
        $roleId = $role->getRoleId();
        $ruleResults = $this->ruleCollection->create()->getByRoles($roleId);
        
        if (empty($role)) {
            throw new NoSuchEntityException(
                __('The role with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }
        $roles = [];
        $roleName = $role->getRoleName();
        foreach ($ruleResults as $rule) {
            if ($rule->getPermission()=='allow') {
                $roles[] = [
                'role' => $roleName,
                'resource_id' => $rule->getResourceId()
                ];
            }
        }

        return $roles;
    }
}
