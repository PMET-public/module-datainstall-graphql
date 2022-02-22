<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Cms\Api\BlockRepositoryInterface;

/**
 * @inheritdoc
 */
class BlockContent implements ResolverInterface
{
    /** @var BlockRepositoryInterface */
    protected $blockRepository;
    
    /** @param BlockRepositoryInterface $blockRepository
     * @param ThemeProviderInterface $themeProvider
     */

    public function __construct(BlockRepositoryInterface $blockRepository)
    {
        $this->blockRepository = $blockRepository;
    }
    
    /**
     * Converts the landing page block id into block identifier
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!empty($value['identifier'])) {
            $block = $this->blockRepository->getById($value['identifier']);
            return $block->getContent();
        } else {
            return null;
        }
    }
}
