<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\AdminUser as AdminUserProvider;

/**
 * Provides customer company user data
 */
class SalesRep implements ResolverInterface
{
    /**
     * @var AdminUserProvider
     */
    private $adminUserDataProvider;

    /**
     * @param AdminUserProvider $adminUserDataProvider
     */
    public function __construct(
        AdminUserProvider $adminUserDataProvider
    ) {
        $this->adminUserDataProvider = $adminUserDataProvider;
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
        if (!empty($value['email'])) {
            $user = $this->adminUserDataProvider->getAdminUserDataByEmail($value['email']);
            if ($field->getName()=='username') {
                return $user['username'];
            } elseif ($field->getName()=='role') {
                return $user['role'];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
