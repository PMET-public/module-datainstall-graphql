<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Category;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

/**
 * @inheritdoc
 */
class CustomDesign implements ResolverInterface
{
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

     /** @var ThemeProviderInterface */
    protected $themeProvider;
    
    /** @param ScopeConfigInterface $scopeConfig
     * @param ThemeProviderInterface $themeProvider
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ThemeProviderInterface $themeProvider
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->themeProvider = $themeProvider;
    }
    
    /**
     * Converts the custom_design ID to theme path
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!empty($value['custom_design'])) {
            $theme = $this->themeProvider->getThemeById($value['custom_design']);
            return $theme->getThemePath();
        } else {
            return null;
        }
    }
}
