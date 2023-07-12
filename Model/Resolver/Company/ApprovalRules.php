<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\PurchaseOrderRule\Api\RuleRepositoryInterface;
use Magento\PurchaseOrderRule\Api\Data\RuleInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Company\Api\RoleRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class ApprovalRules implements ResolverInterface
{
    
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /** @var RoleRepositoryInterface */
    private $roleRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteria;

    /** @var Authentication */
    private $authentication;

   /**
    *
    * @param CustomerRepositoryInterface $customerRepository
    * @param RuleRepositoryInterface $ruleRepository
    * @param RoleRepositoryInterface $roleRepository
    * @param SearchCriteriaBuilder $searchCriteriaBuilder
    * @param Authentication $authentication
    * @return void
    */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        RuleRepositoryInterface $ruleRepository,
        RoleRepositoryInterface $roleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Authentication $authentication
    ) {
        $this->customerRepository = $customerRepository;
        $this->ruleRepository = $ruleRepository;
        $this->roleRepository = $roleRepository;
        $this->searchCriteria = $searchCriteriaBuilder;
        $this->authentication = $authentication;
    }

    /**
     * Get PO Approval Rules
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     * @throws LocalizedException
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

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $rulesData = $this->getRulesData($value['model']->getId());

        return [
            'items' => $rulesData,
        ];
    }

     /**
      * Fetch rule data by field
      *
      * @param int $companyId
      * @return array
      * @throws NoSuchEntityException
      */
    private function getRulesData($companyId): array
    {
        $rulesData = [];
        $search = $this->searchCriteria
            ->addFilter(RuleInterface::KEY_COMPANY_ID, $companyId, 'eq')->create();
            $ruleList = $this->ruleRepository->getList($search)->getItems();
          
        foreach ($ruleList as $rule) {
            $rulesData[]= [
                        "name" => $rule->getName(),
                        "description" => $rule->getDescription(),
                        "is_active" => $rule->isActive(),
                        "apply_to_roles" => $this->getRoleIds($rule->getAppliesToRoleIds()),
                        "conditions_serialized" => $rule->getConditionsSerialized(),
                        "approval_from" => $this->getRoleIds($rule->getApproverRoleIds()),
                        "requires_manager_approval" => $rule->isManagerApprovalRequired(),
                        "requires_admin_approval" => $rule->isAdminApprovalRequired(),
                        "applies_to_all" => $rule->isAppliesToAll(),
                        "created_by" => $this->customerRepository->getById($rule->getCreatedBy())->getEmail()
                    ];
        }
        return $rulesData;
    }
    /**
     * Get names of roles by id, return comma delimited list
     *
     * @param array $roleIds
     * @return string
     * @throws NoSuchEntityException
     */
    private function getRoleIds(array $roleIds): string
    {
        $roleNames = [];
        foreach ($roleIds as $roleId) {
            $roleNames[] = $this->roleRepository->get($roleId)->getRoleName();
        }
        return implode(',', $roleNames);
    }
}
