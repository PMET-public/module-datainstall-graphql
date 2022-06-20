<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Magento\Company\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Company\Model\ResourceModel\UserRole\CollectionFactory;
use Magento\Company\Model\Role\Permission;
use Magento\CompanyGraphQl\Model\Company\IdEncoder;
use Magento\CompanyGraphQl\Model\Company\Role\PermissionsFormatter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Provides customer associated company roles
 */
class Roles implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private $userRoleCollectionFactory;

    /**
     * @var RoleCollectionFactory
     */
    private $roleCollectionFactory;

    /**
     * @var IdEncoder
     */
    private $idEncoder;

    /**
     * @var PermissionsFormatter
     */
    private $permissionsFormatter;

    /**
     * @var Permission
     */
    private $rolePermission;

    /**
     * @param CollectionFactory $userRoleCollectionFactory
     * @param RoleCollectionFactory $roleCollectionFactory
     * @param IdEncoder $idEncoder
     * @param PermissionsFormatter $permissionsFormatter
     * @param Permission $permission
     */
    public function __construct(
        CollectionFactory $userRoleCollectionFactory,
        RoleCollectionFactory $roleCollectionFactory,
        IdEncoder $idEncoder,
        PermissionsFormatter $permissionsFormatter,
        Permission $permission
    ) {
        $this->userRoleCollectionFactory = $userRoleCollectionFactory;
        $this->roleCollectionFactory = $roleCollectionFactory;
        $this->idEncoder = $idEncoder;
        $this->permissionsFormatter = $permissionsFormatter;
        $this->rolePermission = $permission;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $company = $value['model'];
        $companyRoles = $this->roleCollectionFactory->create()
            ->addFieldToFilter('company_id', $company->getId())
            ->setPageSize($args['pageSize'])
            ->setCurPage($args['currentPage']);

        $companyRoleItems = [];

        foreach ($companyRoles as $companyRole) {
            $companyRole->setPermissions($this->rolePermission->getRolePermissions($companyRole));
            $companyRoleItems[] = [
                'id' => $this->idEncoder->encode((string)$companyRole->getId()),
                'name' => $companyRole->getRoleName(),
                'users_count' => $this->userRoleCollectionFactory->create()
                    ->addFieldToFilter('role_id', $companyRole->getId())
                    ->count(),
                'permissions' => $this->permissionsFormatter->format($companyRole)
            ];
        }

        $pageSize = $companyRoles->getPageSize();

        return [
            'items' => $companyRoleItems,
            'total_count' => $companyRoles->count(),
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $companyRoles->getCurPage(),
                'total_pages' => $pageSize ? ((int)ceil($companyRoles->count() / $pageSize)) : 0,
            ]
        ];
    }
}
