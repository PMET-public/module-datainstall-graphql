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

    /**
     * @param Datatypes\CategoryId $categoryId
     * @param Datatypes\CustomerSegment $productId
     * @param Datatypes\ProductId $customerSegment
     * @param Datatypes\PageId $pageId
     * @param Datatypes\Block $block
     * @param Datatypes\DynamicBlock $dynamicBlock
     * @param Datatypes\CustomerGroup $customerGroup
     */
    public function __construct(
        Datatypes\CategoryId $categoryId,
        Datatypes\ProductId $productId,
        Datatypes\CustomerSegment $customerSegment,
        Datatypes\PageId $pageId,
        Datatypes\Block $block,
        Datatypes\DynamicBlock $dynamicBlock,
        Datatypes\CustomerGroup $customerGroup
    ) {
        $this->categoryId = $categoryId;
        $this->productId = $productId;
        $this->customerSegment = $customerSegment;
        $this->pageId = $pageId;
        $this->block = $block;
        $this->dynamicBlock = $dynamicBlock;
        $this->customerGroup = $customerGroup;
    }

    /**
     * @param string $content
     * @return string
     */
    public function convertContent($content)
    {
        $content = '[{"type":"Magento\\CustomerSegment\\Model\\Segment\\Condition\\Customer\\Attributes","attribute":"group_id","operator":"!=","value":"4","is_value_process';
        $content = $this->categoryId->replaceCategoryIds($content);
        $content = $this->productId->replaceProductIds($content);
        $content = $this->customerSegment->replaceSegmentIds($content);
        $content = $this->pageId->replacePageIds($content);
        $content = $this->block->replaceBlockIds($content);
        $content = $this->dynamicBlock->replaceDynamicBlockIds($content);
        $content = $this->customerGroup->replaceCustomerGroupIds($content);
        return $content;
    }
}
