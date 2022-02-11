<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Store;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

/**
 * @inheritdoc
 */
class Theme implements ResolverInterface
{
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

     /** @var ThemeProviderInterface */
    protected $themeProvider;
    
    /** @param ScopeConfigInterface $scopeConfig
     *  @param ThemeProviderInterface $themeProvider
     */

    public function __construct(ScopeConfigInterface $scopeConfig,
    ThemeProviderInterface $themeProvider){
        $this->scopeConfig = $scopeConfig;
        $this->themeProvider = $themeProvider;
    }
    
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $storeId = $context->getExtensionAttributes()->getStore()->getId();
        $themeId = $this->scopeConfig->getValue('design/theme/theme_id','stores',$storeId);      
        $theme = $this->themeProvider->getThemeById($themeId);
        return $theme->getThemePath();
    }
}