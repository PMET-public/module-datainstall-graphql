<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\AdminUsers;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\AdminUser as AdminUserProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use function is_numeric;

class AdminUsers implements ResolverInterface
{
    /** @var AdminUserProvider */
    private $adminUserDataProvider;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param AdminUserProvider $adminUserDataProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        AdminUserProvider $adminUserDataProvider,
        Authentication $authentication
    ) {
        $this->adminUserDataProvider = $adminUserDataProvider;
        $this->authentication = $authentication;
    }

    /**
     * Resolve
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

        $userIdentifiers = $this->getUserIdentifiers($args);
        $userData = $this->getUsersData($userIdentifiers);

        return [
            'items' => $userData,
        ];
    }

    /**
     * Get user identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getUserIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Admin Users should be specified'));
        }

        if ($args['identifiers'][0] == '') {
            $args['identifiers'] = $this->adminUserDataProvider->getAllAdminUserIds();
        }

        return $args['identifiers'];
    }

    /**
     * Get user data
     *
     * @param array $userIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getUsersData(array $userIdentifiers): array
    {
        $usersData = [];
        foreach ($userIdentifiers as $userIdentifier) {
            try {
                if (!is_numeric($userIdentifier)) {
                    $usersData[$userIdentifier] = $this->adminUserDataProvider
                    ->getAdminUserDataByUserName($userIdentifier);
                } else {
                    $usersData[$userIdentifier] = $this->adminUserDataProvider
                    ->getAdminUserDataById($userIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $usersData[$userIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $usersData;
    }
}
