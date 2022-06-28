<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Customer;

use Exception;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\RequisitionList\Model\Config as ModuleConfig;
use Magento\RequisitionListGraphQl\Model\RequisitionList\GetRequisitionList;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * RequisitionList Resolver
 */
class RequisitionList implements ResolverInterface
{
    /**
     * @var GetRequisitionList
     */
    private $getRequisitionList;

    /**
     * @var \Magento\RequisitionList\Model\Config
     */
    private $moduleConfig;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param GetRequisitionList $getRequisitionList
     * @param ModuleConfig $moduleConfig
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        GetRequisitionList $getRequisitionList,
        ModuleConfig $moduleConfig,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->getRequisitionList = $getRequisitionList;
        $this->moduleConfig = $moduleConfig;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Fetches the data from persistence models and format it according to the GraphQL schema.
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$this->moduleConfig->isActive()) {
            throw new GraphQlInputException(__('Requisition List feature is not available.'));
        }
        //get customer by email
        try {
            $customerId = (int) $this->customerRepository->get($value['email'], $value['website_id'])->getId();
        } catch (NoSuchEntityException $e) {
            $customerId = 0;
        }

        $currentPage = $args['currentPage'] ?? 1;
        $pageSize = $args['pageSize'] ?? 20;

        if ($currentPage < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($pageSize < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        return $this->getRequisitionList->execute($customerId, $args);
    }
}
