<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\AdminUser as AdminUserProvider;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class SalesRep implements ResolverInterface
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
     * Get company sales rep
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

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
