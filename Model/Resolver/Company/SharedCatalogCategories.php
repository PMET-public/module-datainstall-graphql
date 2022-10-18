<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SharedCatalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Exception\NoSuchEntityException;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class SharedCatalogCategories implements ResolverInterface
{
    /** @var CategoryManagementInterface */
    private $categoryManagement;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param CategoryManagementInterface $categoryManagementInterface
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        CategoryManagementInterface $categoryManagementInterface,
        CategoryRepositoryInterface $categoryRepository,
        Authentication $authentication
    ) {
        $this->categoryManagement = $categoryManagementInterface;
        $this->categoryRepository = $categoryRepository;
        $this->authentication = $authentication;
    }
    
    /**
     * Get categories assigned to a shared catalog
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
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

        $sharedCatalogCategories = [];

        $categoryIds = $this->categoryManagement->getCategories($value['id']);
        foreach ($categoryIds as $categoryId) {
            $sharedCatalogCategories[]['path'] =
            $this->getCategoryPath($this->categoryRepository->get($categoryId)->getPath());
        }
 
        return $sharedCatalogCategories;
    }
    /**
     * Get string path from numeric path
     *
     * @param string $numericPath
     * @return string
     */
    private function getCategoryPath($numericPath)
    {
        $categoryPath = '';
        $categoryIdArray = explode('/', $numericPath);
        //drop the first element as it will be the root category
        unset($categoryIdArray[0]);
        foreach ($categoryIdArray as $categoryId) {
            $categoryName = $this->categoryRepository->get($categoryId)->getName();
            $categoryPath .='/'.$categoryName;
        }
        return ltrim($categoryPath, '/');
    }
}
