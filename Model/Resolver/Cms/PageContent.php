<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Cms\Api\PageRepositoryInterface;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

/**
 * @inheritdoc
 */
class PageContent implements ResolverInterface
{
    /** @var PageRepositoryInterface */
    private $pageRepository;

    /** @var Converter */
    private $converter;

    /** @var Authentication */
    private $authentication;
    
    /**
     *
     * @param PageRepositoryInterface $pageRepository
     * @param Converter $converter
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        Converter $converter,
        Authentication $authentication
    ) {
        $this->pageRepository = $pageRepository;
        $this->converter = $converter;
        $this->authentication = $authentication;
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
        $this->authentication->authorize();
        
        if (!empty($value['identifier'])) {
            $page = $this->pageRepository->getById($value['page_id']);
            return $this->converter->convertContent($page->getContent());
        } else {
            return null;
        }
    }
}
