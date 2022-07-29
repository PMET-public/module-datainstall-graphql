<?php
/**
 * Copyright © Adobe, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magento\SalesRule\Api\Data\RuleInterface as Rule;
use Magento\SalesRule\Api\RuleRepositoryInterface as RuleRepository;
use Magento\SalesRule\Api\CouponRepositoryInterface as CouponRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;

/**
 * Customer Segment data provider
 */
class CartRule
{
    /**
     * @var RuleCollection
     */
    protected $ruleCollection;

    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

     /**
      * @var CouponRepository
      */
      protected $couponRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepositoryInterface;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteria;

    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @param RuleCollection $ruleCollection
     * @param RuleRepository $ruleRepository
     * @param CouponRepository $couponRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GroupRepositoryInterface $groupRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteria
     * @param Converter $converter
     */
    public function __construct(
        RuleCollection $ruleCollection,
        RuleRepository $ruleRepository,
        CouponRepository $couponRepository,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepositoryInterface,
        SearchCriteriaBuilder $searchCriteria,
        Converter $converter
    ) {
        $this->ruleCollection = $ruleCollection;
        $this->ruleRepository = $ruleRepository;
        $this->couponRepository = $couponRepository;
        $this->websiteRepository = $websiteRepository;
        $this->groupRepositoryInterface = $groupRepositoryInterface;
        $this->searchCriteria = $searchCriteria;
        $this->converter = $converter;
    }

    /**
     * Get rule data by name
     *
     * @param string $ruleName
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
     * @param int $ruleId
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
        $ruleInterface = $this->ruleRepository->getById($rule->getRuleId());
        $extAttributes = $ruleInterface->getExtensionAttributes();
        return [
            'name' => $rule->getName(),
            'site_code' => $this->getWebsiteCodes($rule->getWebsiteIds()),
            'description' => $rule->getDescription(),
            'actions_serialized' => $this->converter->convertContent($rule->getActionsSerialized()),
            'apply_to_shipping' => $rule->getApplyToShipping(),
            'conditions_serialized' => $this->converter->convertContent($rule->getConditionsSerialized()),
            'coupon_code' =>  $this->getCouponCode($rule->getRuleId()),
            'coupon_type' => $rule->getCouponType(),
            'customer_group' => $this->getCustomerGroupNames($rule->getCustomerGroupIds()),
            'discount_amount' => $rule->getDiscountAmount(),
            'discount_qty' => $rule->getDiscountQty(),
            'discount_step' => $rule->getDiscountStep(),
            'is_advanced' => $rule->getIsAdvanced(),
            'is_rss' => $rule->getIsRss(),
            'reward_points_delta' => $extAttributes->getRewardPointsDelta(),
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
    /**
     * Get website codes from ids
     *
     * @param array $siteIds
     * @return string
     */
    private function getWebsiteCodes($siteIds)
    {
        $siteCodes = [];
        foreach ($siteIds as $siteId) {
            $site = $this->websiteRepository->getById($siteId);
            $siteCodes[] = $site->getCode();
        }
        return implode(",", $siteCodes);
    }

    /**
     * Get group names from ids
     *
     * @param array $groupIds
     * @return string
     */
    private function getCustomerGroupNames($groupIds)
    {
        $groupNames = [];
        foreach ($groupIds as $groupId) {
            $group = $this->groupRepositoryInterface->getById($groupId);
            $groupNames[] = $group->getCode();
        }
        return implode(",", $groupNames);
    }

    /**
     * Get coupon code from rule
     *
     * @param array $ruleId
     * @return string
     */
    private function getCouponCode($ruleId)
    {
        $search = $this->searchCriteria
        ->addFilter('rule_id', $ruleId, 'eq')->create()->setPageSize(1)
        ->setCurrentPage(1);
        $couponList = $this->couponRepository->getList($search);
        $coupon = current($couponList->getItems());
        if ($coupon) {
            return $coupon->getCode();
        } else {
            return null;
        }
    }
}
