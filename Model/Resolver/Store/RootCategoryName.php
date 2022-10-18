<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Store;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Exception\NoSuchEntityException;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class RootCategoryName implements ResolverInterface
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var Authentication */
    private $authentication;
    
    /**
     *
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        Authentication $authentication
    ) {
            $this->categoryRepository = $categoryRepository;
            $this->authentication = $authentication;
    }
    
    /**
     * Get Root category name
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authentication->authorize();

        $categoryId = $context->getExtensionAttributes()->getStore()->getRootCategoryId();
        $storeId = $context->getExtensionAttributes()->getStore()->getId();
        /** @var CategoryInterface $category */
        $category = $this->categoryRepository->get($categoryId, $storeId);
        return $category->getName();
    }
}
