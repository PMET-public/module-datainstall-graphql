<?php
/**
 * Copyright © Adobe, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\TargetRule\Model\Rule;
use Magento\TargetRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;

/**
 * Upsell data provider
 */
class Upsell
{
    /**
     * @var RuleCollection
     */
    private $ruleCollection;

    /**
     * @var Rule
     */
    private $rule;

    /**
     * @var CustomerSegment
     */
    private $customerSegment;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @param RuleCollection $ruleCollection
     * @param Rule $rule
     * @param CustomerSegment $customerSegment
     * @param Converter $converter
     */
    public function __construct(
        RuleCollection $ruleCollection,
        Rule $rule,
        CustomerSegment $customerSegment,
        Converter $converter
    ) {
        $this->ruleCollection = $ruleCollection;
        $this->rule = $rule;
        $this->customerSegment = $customerSegment;
        $this->converter = $converter;
    }

    /**
     * Get upsell by name
     *
     * @param string $upsellName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getUpsellRuleDataByName(string $upsellName): array
    {
        $upsellData = $this->fetchUpsellData($upsellName, 'name');

        return $upsellData;
    }

    /**
     * Get group data by id
     *
     * @param int $upsellId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getUpsellRuleDataById(int $upsellId): array
    {
        $upsellData = $this->fetchUpsellData($upsellId, 'rule_id');

        return $upsellData;
    }

    /**
     * Fetch upsell data by either id or field
     *
     * @param mixed $identifier
     * @param string $field
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchUpsellData($identifier, string $field): array
    {
        $ruleResults = $this->ruleCollection->create()->addFieldToFilter($field, [$identifier])->getItems();

        if (empty($ruleResults)) {
            throw new NoSuchEntityException(
                __('The related product rule with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }
        /** @var Rule $rule */
        $rule = current($ruleResults);
        if (empty($rule)) {
            throw new NoSuchEntityException(
                __('The realated product rule with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }

        return [
            'name' => $rule->getName(),
            'conditions_serialized' => $this->converter->convertContent($rule->getConditionsSerialized()),
            'actions_serialized' => $this->converter->convertContent($rule->getActionsSerialized()),
            'positions_limit' => $rule->getPositionsLimit(),
            'apply_to' => $this->getApplyToText((int)$rule->getApplyTo()),
            'sort_order' => $rule->getSortOrder(),
            'customer_segments' => $this->getCustomerSegments($rule),
        ];
    }

    /**
     * Convert apply to id to text
     *
     * @param Int $applyToId
     * @return string
     */

    private function getApplyToText($applyToId)
    {
        switch ($applyToId) {
            case Rule::UP_SELLS:
                $applyTo = "upsell";
                break;

            case Rule::CROSS_SELLS:
                $applyTo = "crosssell";
                break;

            default:
                $applyTo = "related";
                break;
        }
        return $applyTo;
    }

    /**
     * Get customer segments from rule
     *
     * @param Rule $rule
     * @return string
     */
    private function getCustomerSegments($rule)
    {
        $segmentIds = $rule->getCustomerSegmentIds();
        if (!$segmentIds) {
            $segmentNames = 'all';
        } else {
            $segmentNames=[];
            foreach ($segmentIds as $segmentId) {
                $segmentNames[]=$this->customerSegment->getSegmentDataById((int)$segmentId)['name'];
            }
            $segmentNames = implode(',', $segmentNames);
        }
        return $segmentNames;
    }
}
