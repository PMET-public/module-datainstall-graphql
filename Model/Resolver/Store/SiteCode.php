<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Store;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * @inheritdoc
 */
class SiteCode implements ResolverInterface
{
    
    /** @var WebsiteRepositoryInterface */
    protected $websiteRepositoryInterface;

    /** @param WebsiteRepositoryInterface $websiteRepositoryInterface */
    public function __construct(WebsiteRepositoryInterface $websiteRepositoryInterface)
    {
        $this->websiteRepositoryInterface = $websiteRepositoryInterface;
    }
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $websiteId = $context->getExtensionAttributes()->getStore()->getWebsiteId();
        return $this->websiteRepositoryInterface->get($websiteId)->getCode();
    }
}
