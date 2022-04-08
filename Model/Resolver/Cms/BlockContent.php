<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Cms\Api\BlockRepositoryInterface;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;

/**
 * @inheritdoc
 */
class BlockContent implements ResolverInterface
{
    /** @var BlockRepositoryInterface */
    protected $blockRepository;

    /** @var Converter */
    protected $converter;
    
    /** @param BlockRepositoryInterface $blockRepository
     * @param Converter $converter
     */

    public function __construct(
        BlockRepositoryInterface $blockRepository,
        Converter $converter
    ) {
        $this->blockRepository = $blockRepository;
        $this->converter = $converter;
    }
    
    /**
     * returns raw content of the block
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!empty($value['identifier'])) {
            $block = $this->blockRepository->getById($value['identifier']);
            return $this->converter->convertContent($block->getContent());
        } else {
            return null;
        }
    }
}
