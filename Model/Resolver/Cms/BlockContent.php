<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Framework\GraphQl\Config\Element\Field;
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
     */

    public function __construct(BlockRepositoryInterface $blockRepository)
    {
        $this->blockRepository = $blockRepository;
    }
    
    /**
     * returns raw content of the block
     * @inheritdoc
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
