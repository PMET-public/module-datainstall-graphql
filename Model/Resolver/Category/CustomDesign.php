<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Category;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

/**
 * @inheritdoc
 */
class CustomDesign implements ResolverInterface
{
    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var ThemeProviderInterface */
    private $themeProvider;

    /** @var Authentication */
    private $authentication;
    
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
     * Converts the custom_design ID to theme path
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

        if (!empty($value['custom_design'])) {
            $theme = $this->themeProvider->getThemeById($value['custom_design']);
            return $theme->getThemePath();
        } else {
            return null;
        }
    }
}
