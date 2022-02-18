<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Store;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * @inheritdoc
 */
class RootCategoryName implements ResolverInterface
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;
    
    /** @param CategoryRepositoryInterface $categoryRepository */

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }
    
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $categoryId = $context->getExtensionAttributes()->getStore()->getRootCategoryId();
        $storeId = $context->getExtensionAttributes()->getStore()->getId();
        /** @var CategoryInterface $category */
        $category = $this->categoryRepository->get($categoryId, $storeId);
        return $category->getName();
    }
}
