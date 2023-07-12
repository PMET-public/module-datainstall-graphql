<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Category;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * Gets the path by category names as needed for the Data Installer
 */
class NamePath implements ResolverInterface
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;
    
    /** @param CategoryRepositoryInterface $categoryRepository */

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }
    
    /**
     * Get category name path
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
        $namePath = $this->getNamePath($value['path']);
        return $namePath;
    }

    /**
     * Get name path by number path
     *
     * @param string $path
     * @return string
     */
    private function getNamePath($path)
    {
        //remove the first 2 and last elements
        $idPath = explode("/", $path);
        array_splice($idPath, 0, 2);
        array_splice($idPath, -1, 1);
        $namePath=[];
        foreach ($idPath as $categoryId) {
            $namePath[]=$this->categoryRepository->get($categoryId)->getName();
        }
        //return implode("/",$namePath);
        return implode("/", str_replace('/', '\\/', $namePath));
    }
}
