<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Attribute;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;

/**
 * Resolve data for custom attribute metadata requests
 */
class AdminProperties implements ResolverInterface
{

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $storeId = $context->getExtensionAttributes()->getStore()->getId();
        $attributeCode = $value['attribute_code'];
        $attribute = $this->attributeRepository->get('catalog_product', $attributeCode);
        return $this->getStorefrontProperties($attribute, $storeId);
    }

    /**
     * Format storefront properties
     *
     * @param AttributeInterface $attribute
     * @return array
     */
    private function getStorefrontProperties($attribute, $storeId)
    {
        return [
            'attribute_set'=> 'foo',
            'frontend_label'=> $this->getFrontEndLabel($attribute, $storeId),
            'is_visible'=> $attribute->getIsVisible(),
            'is_searchable'=> $attribute->getIsSearchable(),
            'is_comparable'=> $attribute->getIsComperable(),
            'is_html_allowed_on_front'=> $attribute->getIsHtmlAllowedOnFront(),
            'is_used_for_price_rules'=> $attribute->getIsUsedForPriceRules(),
            'used_for_sort_by'=> $attribute->getUsedForSortBy(),
            'is_visible_in_advanced_search'=> $attribute->getIsVisibleInAdvancedSearch(),
            'is_wysiwyg_enabled'=> $attribute->getIsWysiwygEnabled(),
            'is_used_for_promo_rules'=> $attribute->getIsUsedForPromoRules(),
            'is_required_in_admin_store'=> $attribute->getIsRequiredInAdminStore(),
            'is_used_in_grid'=> $attribute->getIsUsedInGrid(),
            'is_visible_in_grid'=> $attribute->getIsVisibleInGrid(),
            'is_filterable_in_grid'=> $attribute->getIsFilterableInGrid(),
            'search_weight'=> $attribute->getSearchWeight(),
            'is_pagebuilder_enabled'=> $attribute->getIsPagebuilderEnabled(),
            'additional_data'=> $attribute->getAdditionalData()
        ];
    }

    /**
     * Return enum for resolving use in layered navigation
     * @param AttributeInterface $attribute
     * @param $storeId
     * @return string
     */
    private function getFrontEndLabel(AttributeInterface $attribute, $storeId)
    {
        $labels = $attribute->getFrontendLabels();
        //set default if store label is not found
        $frontLabel = $attribute->getDefaultFrontendLabel();
        foreach ($labels as $label) {
            if ($label->getStoreId()==$storeId) {
                $frontLabel =  $label->getLabel();
                break;
            }
        }
        return $frontLabel;
    }
}