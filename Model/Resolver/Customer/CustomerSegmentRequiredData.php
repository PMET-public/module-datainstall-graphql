<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Customer;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerSegment\Model\Segment;
use Magento\CustomerSegment\Model\ResourceModel\Segment\CollectionFactory as SegmentCollection;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use MagentoEse\DataInstallGraphQl\Model\Converter\RequiredDataInterfaceFactory;

class CustomerSegmentRequiredData implements ResolverInterface
{
    /** @var SegmentCollection */
    protected $segmentCollection;

    /** @var Converter */
    protected $converter;

    /** @var Authentication */
    protected $authentication;

    /** @var RequiredDataInterfaceFactory */
    protected $requiredDataFactory;
    
    /**
     * SegmentCollection
     * @param SegmentCollection $segmentCollection
     * @param Converter $converter
     * @param Authentication $authentication
     * @param RequiredDataInterfaceFactory $requiredDataFactory
     * @return void
     */
    public function __construct(
        SegmentCollection $segmentCollection,
        Converter $converter,
        Authentication $authentication,
        RequiredDataInterfaceFactory $requiredDataFactory
    ) {
        $this->segmentCollection = $segmentCollection;
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

        if (!empty($value['segment_id'])) {
            $requiredData = $this->requiredDataFactory->create();
            /** @var Rule $rule */
            $segementResults = $this->segmentCollection->create()
            ->addFieldToFilter('segment_id', $value['segment_id'])->getItems();
            $segment = current($segementResults);
            $returnData = $requiredData->
            getRequiredData($segment->getConditionsSerialized());
            return $returnData;
        } else {
            return null;
        }
    }
}
