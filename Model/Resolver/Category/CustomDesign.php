<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Category;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class CustomDesign implements ResolverInterface
{

    /** @var ThemeProviderInterface */
    private $themeProvider;

    /** @var Authentication */
    private $authentication;
    
    /**
     *
     * @param ThemeProviderInterface $themeProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        ThemeProviderInterface $themeProvider,
        Authentication $authentication
    ) {
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
