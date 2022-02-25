<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Cms\Api\PageRepositoryInterface;

/**
 * @inheritdoc
 */
class Pages implements ResolverInterface
{
    /** @var PageRepositoryInterface */
    protected $pageRepository;
    
    /** @param PageRepositoryInterface $pageRepository
     */

    public function __construct(PageRepositoryInterface $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }
    
    /**
     * Converts the landing page page id into page identifier
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!empty($value['identifier'])) {
            $page = $this->pageRepository->getById($value['page_id']);
            return $page->getContent();
        } else {
            return null;
        }
    }
}
