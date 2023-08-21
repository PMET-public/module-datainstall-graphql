<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Marketing;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\TargetRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use MagentoEse\DataInstallGraphQl\Model\Converter\RequiredDataInterfaceFactory;

class UpsellRequiredData implements ResolverInterface
{
    /** @var RuleCollection */
    protected $ruleCollection;

    /** @var Converter */
    protected $converter;

    /** @var Authentication */
    protected $authentication;

    /** @var RequiredDataInterfaceFactory */
    protected $requiredDataFactory;
    
    /**
     *
     * @param RuleCollection $ruleCollection
     * @param Converter $converter
     * @param Authentication $authentication
     * @param RequiredDataInterfaceFactory $requiredDataFactory
     * @return void
     */
    public function __construct(
        RuleCollection $ruleCollection,
        Converter $converter,
        Authentication $authentication,
        RequiredDataInterfaceFactory $requiredDataFactory
    ) {
        $this->ruleCollection = $ruleCollection;
        $this->converter = $converter;
        $this->authentication = $authentication;
        $this->requiredDataFactory = $requiredDataFactory;
    }
    
    /**
     * Returns other elements required by the block
     *
     * @param Field $field
     * @param Context $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     * @return mixed
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authentication->authorize();

        if (!empty($value['rule_id'])) {
            $requiredData = $this->requiredDataFactory->create();
            /** @var Rule $rule */
            $ruleResults = $this->ruleCollection->create()->addFieldToFilter('rule_id', $value['rule_id'])->getItems();
            $rule = current($ruleResults);
            $returnData = $requiredData->
            getRequiredData($rule->getActionsSerialized().' '.$rule->getConditionsSerialized());
            return $returnData;
        } else {
            return null;
        }
    }
}
