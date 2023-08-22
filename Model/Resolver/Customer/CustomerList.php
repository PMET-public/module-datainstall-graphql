<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class CustomerList implements ResolverInterface
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteria;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteria
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteria,
        Authentication $authentication
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteria = $searchCriteria;
        $this->authentication = $authentication;
    }

    /**
     * Get List of customers
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
        $search = $this->searchCriteria->create();
        $customerList = $this->customerRepository->getList($search)->getItems();
        $customerData = [];
        foreach ($customerList as $customer) {
            $customerData[]=[
                'email' => $customer->getEmail(),
                'firstname' => $customer->getFirstname(),
                'lastname' => $customer->getLastname(),
                'customer_id' => $customer->getId()
            ];
        }

        return [
            'items' => $customerData,
        ];
    }
}
