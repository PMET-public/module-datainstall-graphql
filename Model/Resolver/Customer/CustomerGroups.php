<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Customer;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\CustomerGroup as CustomerGroupProvider;
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

class CustomerGroups implements ResolverInterface
{
    /** @var CustomerGroupProvider */
    private $customerGroupDataProvider;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param CustomerGroupProvider $customerGroupProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        CustomerGroupProvider $customerGroupProvider,
        Authentication $authentication
    ) {
        $this->customerGroupDataProvider = $customerGroupProvider;
        $this->authentication = $authentication;
    }

    /**
     * Get Customer groups
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

        $groupIdentifiers = $this->getGroupIdentifiers($args);
        $groupData = $this->getGroupsData($groupIdentifiers);

        return [
            'items' => $groupData,
        ];
    }

    /**
     * Get group identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getGroupIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Customer Groups should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get group data
     *
     * @param array $groupIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getGroupsData(array $groupIdentifiers): array
    {
        $groupsData = [];
        foreach ($groupIdentifiers as $groupIdentifier) {
            try {
                if (!is_numeric($groupIdentifier)) {
                    $groupsData[$groupIdentifier] = $this->customerGroupDataProvider
                        ->getGroupDataByName($groupIdentifier);
                } else {
                    $groupsData[$groupIdentifier] = $this->customerGroupDataProvider
                        ->getGroupDataById((int)$groupIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $groupsData[$groupIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $groupsData;
    }
}
