<?php

namespace MagentoEse\DataInstallGraphQl\Model\Converter;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;
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
use MagentoEse\DataInstallGraphQl\Model\Converter\RequiredDataInterface;

class RequiredData implements RequiredDataInterface
{
    /** @var string */
    protected $type;

    /** @var int */
    protected $id;

    /** @var string */
    protected $identifier;

    /** @var string */
    protected $name;

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
     * Get required data
     *
     * @param string $content
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws StateException
     * @throws InputException
     */
    public function getRequiredData($content) : array
    {
        $requiredData = [];
        $requiredData = array_merge(
            $requiredData,
            $this->categoryId->getRequiredCategoryIds($content, self::CATEGORIES)
        );

        $requiredData = array_merge(
            $requiredData,
            $this->productId->getRequiredProductIds($content, self::PRODUCTS)
        );

        $requiredData = array_merge(
            $requiredData,
            $this->customerSegment->getRequiredSegments($content, self::CUSTOMER_SEGMENTS)
        );

        $requiredData = array_merge(
            $requiredData,
            $this->pageId->getRequiredPageIds($content, self::PAGES)
        );

        $requiredData = array_merge(
            $requiredData,
            $this->block->getRequiredBlocks($content, self::BLOCKS)
        );

        $requiredData = array_merge(
            $requiredData,
            $this->dynamicBlock->getRequiredDynamicBlocks($content, self::DYNAMIC_BLOCKS)
        );

        $requiredData = array_merge(
            $requiredData,
            $this->customerGroup->getRequiredCustomerGroups($content, self::CUSTOMER_GROUPS)
        );

        $requiredData = array_merge(
            $requiredData,
            $this->productAttributeSet->getRequiredProductAttributeSets($content, self::PRODUCT_ATTRIBUTE_SETS)
        );

        $requiredData = array_merge(
            $requiredData,
            $this->productAttribute->getRequiredAttributeOptions($content, self::PRODUCT_ATTRIBUTES)
        );

        $requiredData = array_merge(
            $requiredData,
            $this->customerAttribute->getRequiredAttributeOptions($content, self::CUSTOMER_ATTRIBUTES)
        );
        $requiredData = $this->eliminateDuplicateArrays($requiredData);
        return $requiredData;
    }

     /**
     * Eliminate duplicate arrays within an array
     *
     * @param array $array
     * @return array
     */
    private function eliminateDuplicateArrays(array $array): array
    {
        $serializedArray = array_map('serialize', $array);
        $uniqueSerializedArray = array_unique($serializedArray);
        return array_map('unserialize', $uniqueSerializedArray);
    }
}
