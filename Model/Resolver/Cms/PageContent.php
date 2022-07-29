<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Cms\Api\PageRepositoryInterface;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;

/**
 * @inheritdoc
 */
class PageContent implements ResolverInterface
{
    /** @var PageRepositoryInterface */
    protected $pageRepository;

    /** @var Converter */
    protected $converter;
    
    /** @param PageRepositoryInterface $pageRepository
     * @param Converter $converter
     */

    public function __construct(
        PageRepositoryInterface $pageRepository,
        Converter $converter
    ) {
        $this->pageRepository = $pageRepository;
        $this->converter = $converter;
    }
    
    /**
     * Converts the landing page page id into page identifier
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
        if (!empty($value['identifier'])) {
            $page = $this->pageRepository->getById($value['page_id']);
            return $this->converter->convertContent($page->getContent());
        } else {
            return null;
        }
    }
}
