<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Converter;

use MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes\Block;
use MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes\CategoryId;
use MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes\CustomerAttribute;
use MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes\CustomerGroup;
use MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes\CustomerSegment;
use MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes\DynamicBlock;
use MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes\PageId;
use MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes\ProductAttribute;
use MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes\ProductAttributeSet;
use MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes\ProductId;

class Converter
{
    /** @var CategoryId */
    protected $categoryId;

    /** @var ProductId */
    protected $productId;

    /** @var CustomerSegment */
    protected $customerSegment;

    /** @var PageId */
    protected $pageId;

    /** @var Block */
    protected $block;

    /** @var DynamicBlock */
    protected $dynamicBlock;

    /** @var CustomerGroup */
    protected $customerGroup;

    /** @var ProductAttributeSet */
    protected $productAttributeSet;

    /** @var ProductAttribute */
    protected $productAttribute;

    /** @var CustomerAttribute */
    protected $customerAttribute;

    /**
     * Converter constructor
     *
     * @param CategoryId $categoryId
     * @param CustomerSegment $productId
     * @param ProductId $customerSegment
     * @param PageId $pageId
     * @param Block $block
     * @param DynamicBlock $dynamicBlock
     * @param CustomerGroup $customerGroup
     * @param ProductAttributeSet $productAttributeSet
     * @param ProductAttribute $productAttribute
     * @param CustomerAttribute $customerAttribute
     */
    public function __construct(
        CategoryId $categoryId,
        ProductId $productId,
        CustomerSegment $customerSegment,
        PageId $pageId,
        Block $block,
        DynamicBlock $dynamicBlock,
        CustomerGroup $customerGroup,
        ProductAttributeSet $productAttributeSet,
        ProductAttribute $productAttribute,
        CustomerAttribute $customerAttribute
    ) {
        $this->categoryId = $categoryId;
        $this->productId = $productId;
        $this->customerSegment = $customerSegment;
        $this->pageId = $pageId;
        $this->block = $block;
        $this->dynamicBlock = $dynamicBlock;
        $this->customerGroup = $customerGroup;
        $this->productAttributeSet = $productAttributeSet;
        $this->productAttribute = $productAttribute;
        $this->customerAttribute = $customerAttribute;
    }

    /**
     * Replace ids with tokens
     *
     * @param string $content
     * @return string
     */
    public function convertContent($content)
    {
        $content = $this->categoryId->replaceCategoryIds($content);
        $content = $this->productId->replaceProductIds($content);
        $content = $this->customerSegment->replaceSegmentIds($content);
        $content = $this->pageId->replacePageIds($content);
        $content = $this->block->replaceBlockIds($content);
        $content = $this->dynamicBlock->replaceDynamicBlockIds($content);
        $content = $this->customerGroup->replaceCustomerGroupIds($content);
        $content = $this->productAttributeSet->replaceAttributeSetIds($content);
        $content = $this->productAttribute->replaceAttributeOptionIds($content);
        $content = $this->customerAttribute->replaceAttributeOptionIds($content);
        return $content;
    }
}
