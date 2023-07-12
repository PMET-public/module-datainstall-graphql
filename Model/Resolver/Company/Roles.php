<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Exception;
use Magento\Company\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Company\Model\ResourceModel\UserRole\CollectionFactory;
use Magento\Company\Model\Role\Permission;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\CompanyGraphQl\Model\Company\Role\PermissionsFormatter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use Zend_Db_Select_Exception;

class Roles implements ResolverInterface
{
    /** @var CollectionFactory */
    private $userRoleCollectionFactory;

    /** @var RoleCollectionFactory */
    private $roleCollectionFactory;

    /** @var Uid */
    private $idEncoder;

    /** @var PermissionsFormatter */
    private $permissionsFormatter;

    /** @var Permission */
    private $rolePermission;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param CollectionFactory $userRoleCollectionFactory
     * @param RoleCollectionFactory $roleCollectionFactory
     * @param Uid $idEncoder
     * @param PermissionsFormatter $permissionsFormatter
     * @param Permission $permission
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        CollectionFactory $userRoleCollectionFactory,
        RoleCollectionFactory $roleCollectionFactory,
        Uid $idEncoder,
        PermissionsFormatter $permissionsFormatter,
        Permission $permission,
        Authentication $authentication
    ) {
        $this->userRoleCollectionFactory = $userRoleCollectionFactory;
        $this->roleCollectionFactory = $roleCollectionFactory;
        $this->idEncoder = $idEncoder;
        $this->permissionsFormatter = $permissionsFormatter;
        $this->rolePermission = $permission;
        $this->authentication = $authentication;
    }

   /**
    * Get Company user roles
    *
    * @param Field $field
    * @param ContextInterface $context
    * @param ResolveInfo $info
    * @param array|null $value
    * @param array|null $args
    * @return mixed|Value
    * @throws GraphQlInputException
    * @throws LocalizedException
    * @throws Zend_Db_Select_Exception
    * @throws Exception
    */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

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
        if ($company) {
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
        } else {
            return[
                'items' => [],
                'total_count' => 0,
                'page_info' => [
                    'page_size' => 0,
                    'current_page' => 0,
                    'total_pages' => 0,
                ]
            ];
        }
    }
}
