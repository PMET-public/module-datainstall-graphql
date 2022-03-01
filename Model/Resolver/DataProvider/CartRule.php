<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magento\SalesRule\Model\Data\Rule;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;

/**
 * Customer Segment data provider
 */
class CartRule
{
    /**
     * @var RuleCollection
     */
    private $ruleCollection;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

     /**
      * @var GroupRepositoryInterface
      */
    private $groupRepositoryInterface;

    /**
     * @param RuleCollection $ruleCollection
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GroupRepositoryInterface $groupRepositoryInterface
     */
    public function __construct(
        RuleCollection $ruleCollection,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepositoryInterface
    ) {
        $this->ruleCollection = $ruleCollection;
        $this->websiteRepository = $websiteRepository;
        $this->groupRepositoryInterface = $groupRepositoryInterface;
    }

    /**
     * Get rule data by name
     *
     * @param string $segmentIdentifier
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCartRuleDataByName(string $ruleName): array
    {
        $ruleData = $this->fetchRuleData($ruleName, 'name');

        return $ruleData;
    }

    /**
     * Get block data by block_id
     *
     * @param int $blockId
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCartRuleDataById(int $ruleId): array
    {
        $ruleData = $this->fetchRuleData($ruleId, 'rule_id');

        return $ruleData;
    }

    /**
     * Fetch rule data by either id or name field
     *
     * @param mixed $identifier
     * @param string $field
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchRuleData($identifier, string $field): array
    {
        $ruleResults = $this->ruleCollection->create()->addFieldToFilter($field, [$identifier])->getItems();

        if (empty($ruleResults)) {
            throw new NoSuchEntityException(
                __('The rule with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }
        /** @var Rule $rule */
        $rule = current($ruleResults);

        return [
            'name' => $rule->getName(),
            'site_code' => $this->getWebsiteCodes($rule->getWebsiteIds()),
            'description' => $rule->getDescription(),
            'actions_serialized' => $rule->getActionCondition(),
            'apply_to_shipping' => $rule->getApplyToShipping(),
            'conditions_serialized' => $rule->getConditionsSerialized(),
            'coupon_code' => $rule->getCouponCode(),
            'coupon_type' => $rule->getCouponType(),
            'customer_group' => $this->getCustomerGroupNames($rule->getCustomerGroupIds()),
            'discount_amount' => $rule->getDiscountAmount(),
            'discount_qty' => $rule->getDiscountQty(),
            'is_advanced' => $rule->getIsAdvanced(),
            'is_rss' => $rule->getIsRss(),
            'reward_points_delta' => $rule->getIsRss(),
            'simple_action' => $rule->getSimpleAction(),
            'simple_free_shipping' => $rule->getSimpleFreeShipping(),
            'sort_order' => $rule->getSortOrder(),
            'stop_rules_processing' => $rule->getStopRulesProcessing(),
            'times_used' => $rule->getTimesUsed(),
            'use_auto_generation' => $rule->getUseAutoGeneration(),
            'uses_per_coupon' => $rule->getUsesPerCoupon(),
            'uses_per_customer' => $rule->getUsesPerCustomer()
           
        ];
    }

    private function getWebsiteCodes($siteIds)
    {
        $siteCodes = [];
        foreach ($siteIds as $siteId) {
            $site = $this->websiteRepository->getById($siteId);
            $siteCodes[] = $site->getCode();
        }
        return implode(",", $siteCodes);
    }

    private function getCustomerGroupNames($groupIds)
    {
        $groupNames = [];
        foreach ($groupIds as $groupId) {
            $group = $this->groupRepositoryInterface->getById($groupId);
            $groupNames[] = $group->getCode();
        }
        return implode(",", $groupNames);
    }
}
