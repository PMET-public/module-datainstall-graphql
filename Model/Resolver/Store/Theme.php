<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Store;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

/**
 * @inheritdoc
 */
class Theme implements ResolverInterface
{
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

     /** @var ThemeProviderInterface */
    protected $themeProvider;

    /** @var Authentication */
    protected $authentication;
    
    /**
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ThemeProviderInterface $themeProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ThemeProviderInterface $themeProvider,
        Authentication $authentication
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->themeProvider = $themeProvider;
        $this->authentication = $authentication;
    }
    
    /**
     * Resolve
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authentication->authorize();
        $storeId = $context->getExtensionAttributes()->getStore()->getId();
        $themeId = $this->scopeConfig->getValue('design/theme/theme_id', 'stores', $storeId);
        $theme = $this->themeProvider->getThemeById($themeId);
        return $theme->getThemePath();
    }
}
