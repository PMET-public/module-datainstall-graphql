<?php

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Export;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class AllChildCategories
{

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get all child ategory ids for a given category
     *
     * @param mixed $categoryIds
     * @param mixed $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAllCategoryIds($categoryIds, $storeId = null)
    {
        $allIds = [];
        //get category
        foreach ($categoryIds as $categoryId) {
            $category = $this->categoryRepository->get($categoryId, $storeId);
            //if its an anchor category, get child ids
            if ($category->getIsAnchor()) {
                //phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                $allIds = array_merge($allIds, $this->recurse($categoryId, $storeId));
            }
        }
        
        return $allIds;
    }
    /**
     * Recurse through child categories
     *
     * @param int $categoryId
     * @param int $storeId
     * @return string[]|false|void
     * @throws NoSuchEntityException
     */
    private function recurse($categoryId, $storeId = null)
    {
            $category = $this->categoryRepository->get($categoryId, $storeId);
            $childIds = explode(',', $category->getChildren());
            //recurse childIds get all child categories
        foreach ($childIds as $id) {
            if ($id=='') {
                return [];
            } else {
                //phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                $childIds = array_merge($childIds, $this->recurse($id, $storeId));
            }
        }
            return $childIds;
    }
}
