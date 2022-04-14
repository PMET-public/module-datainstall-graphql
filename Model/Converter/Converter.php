<?php
namespace MagentoEse\DataInstallGraphQl\Model\Converter;

class Converter
{
    /** @var Datatypes\CategoryId */
    protected $categoryId;

    /** @var Datatypes\ProductId */
    protected $productId;

    /** @var Datatypes\CustomerSegment */
    protected $customerSegment;

    /** @var Datatypes\PageId */
    protected $pageId;

    /** @var Datatypes\Block */
    protected $block;

    /** @var Datatypes\DynamicBlock */
    protected $dynamicBlock;

    /** @var Datatypes\CustomerGroup */
    protected $customerGroup;

    /** @var Datatypes\ProductAttributeSet */
    protected $productAttributeSet;

    /** @var Datatypes\ProductAttribute */
    protected $productAttribute;

    /** @var Datatypes\CustomerAttribute */
    protected $customerAttribute;

    /**
     * @param Datatypes\CategoryId $categoryId
     * @param Datatypes\CustomerSegment $productId
     * @param Datatypes\ProductId $customerSegment
     * @param Datatypes\PageId $pageId
     * @param Datatypes\Block $block
     * @param Datatypes\DynamicBlock $dynamicBlock
     * @param Datatypes\CustomerGroup $customerGroup
     * @param Datatypes\ProductAttributeSet $productAttributeSet
     * @param Datatypes\ProductAttribute $productAttribute
     * @param Datatypes\CustomerAttribute $customerAttribute
     */
    public function __construct(
        Datatypes\CategoryId $categoryId,
        Datatypes\ProductId $productId,
        Datatypes\CustomerSegment $customerSegment,
        Datatypes\PageId $pageId,
        Datatypes\Block $block,
        Datatypes\DynamicBlock $dynamicBlock,
        Datatypes\CustomerGroup $customerGroup,
        Datatypes\ProductAttributeSet $productAttributeSet,
        Datatypes\ProductAttribute $productAttribute,
        Datatypes\CustomerAttribute $customerAttribute
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
