<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Attribute;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class AttributeList implements ResolverInterface
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var Authentication */
    private $authentication;

    private const INSTALLED_PRODUCT_ATTRIBUTES = ["color","cost","manufacturer"];
    private const INSTALLED_CATEGORY_ATTRIBUTES = ["automatic_sorting"];

    /**
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Authentication $authentication
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->authentication = $authentication;
    }

    /**
     * Return All Attributes for UI
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();
        if (!isset($args['entityType'])) {
            throw new GraphQlInputException(__('"entityType" should be specified'));
        }
        //$storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $attributeData = $this->getAttributeData($args['entityType'][0]);

        return [
            'items' => $attributeData
        ];
    }

    /**
     * Get all attribute data
     *
     * @return array
     * @param string $entityType
     * @throws GraphQlNoSuchEntityException
     */
    private function getAttributeData(string $entityType): array
    {
        $attributeData = [];
        $search = $this->searchCriteriaBuilder
            ->addFilter(AttributeInterface::IS_USER_DEFINED, true, 'eq')
            ->create();
        $attributeList = $this->attributeRepository->getList($entityType, $search)->getItems();
        
        foreach ($attributeList as $attribute) {
            $attributeData[] = [
                'attribute_code' => $attribute->getAttributeCode(),
                'default_label' => $attribute->getDefaultFrontendLabel(),
                'is_user_defined' => $attribute->getIsUserDefined(),
                'scope' => $attribute->getScope(),
                'is_core' => $this->isInCore($attribute->getAttributeCode(), $entityType)
            ];
        }
        return $attributeData;
    }

    /**
     * Check if attribute is in core
     *
     * @param string $attributeCode
     * @param string $entityType
     * @return bool
     */
    private function isInCore(string $attributeCode, $entityType): bool
    {
        switch ($entityType) {
            case "catalog_product":
                return in_array($attributeCode, self::INSTALLED_PRODUCT_ATTRIBUTES, true);
            case "catalog_category":
                return in_array($attributeCode, self::INSTALLED_CATEGORY_ATTRIBUTES, true);
            default:
                return false;
        }
    }
}
