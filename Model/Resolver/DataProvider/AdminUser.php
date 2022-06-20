<?php
/**
 * Copyright © Adobe, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollection;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollection;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer Role data provider
 */
class AdminUser
{
    /**
     * @var RoleCollection
     */
    private $roleCollection;

    /**
     * @var UserCollection
     */
    private $userCollection;

    /**
     * @param RoleCollection $roleCollection
     * @param UserCollection $ruleCollection
     */
    public function __construct(
        RoleCollection $roleCollection,
        UserCollection $userCollection
    ) {
        $this->roleCollection = $roleCollection;
        $this->userCollection = $userCollection;
    }

    /**
     * Get admin user by username
     *
     * @param string $roleName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAdminUserDataByUsername(string $userName): array
    {
        $roleData = $this->fetchRoleData($userName, 'username');

        return $roleData;
    }

    /**
     * Get admin user by username
     *
     * @param string $roleName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAdminUserDataByEmail(string $email): array
    {
        $roleData = $this->fetchRoleData($email, 'email');

        return $roleData;
    }

    /**
     * Get admin user by id
     *
     * @param string $userId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAdminUserDataById(int $userId): array
    {
        $roleData = $this->fetchRoleData($userId, 'user_id');

        return $roleData;
    }

    /**
     * Fetch admin user by field
     *
     * @param mixed $identifier
     * @param string $field
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchRoleData($identifier, string $field): array
    {
        $userResults = $this->userCollection->create()->addFieldToFilter($field, [$identifier])->getItems();
        $user = current($userResults);
        
        if (empty($user)) {
            throw new NoSuchEntityException(
                __('The user with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }

        $userId = $user->getUserId();
        $roleResults = $this->roleCollection->create()->addFieldToFilter('user_id', $userId)->getItems();
        $role = current($roleResults);
        $parentId = $role->getParentId();
        $roleResults = $this->roleCollection->create()->addFieldToFilter('role_id', $parentId)->getItems();
        $role = current($roleResults);

        return [
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'password' => 'Password1',
            'role'  => $role->getRoleName()
        ];
    }
}
