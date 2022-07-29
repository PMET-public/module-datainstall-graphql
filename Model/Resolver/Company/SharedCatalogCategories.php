<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\SharedCatalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class SharedCatalogCategories implements ResolverInterface
{
    /** @var CategoryManagementInterface */
    protected $categoryManagement;

     /** @var CategoryRepositoryInterface */
     protected $categoryRepository;

    /**
     * @param CategoryManagementInterface $categoryManagementInterface
     * @param CategoryRepositoryInterface $categoryRepository
     */

    public function __construct(
        CategoryManagementInterface $categoryManagementInterface,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryManagement = $categoryManagementInterface;
        $this->categoryRepository = $categoryRepository;
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
