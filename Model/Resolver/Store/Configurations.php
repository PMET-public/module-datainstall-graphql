<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Store;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\Configuration as ConfigurationDataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Configurations implements ResolverInterface
{
    /** @var array */
    private array $defaultPaths=[
        'catalog/custom_options/use_calendar',
        'design/head/includes',
        'design/head/shortcut_icon',
        'design/header/logo_src',
        'design/header/welcome',
        'general/store_information/name',
        'web/default/cms_home_page'];
    
    /** @var array */
    private array $b2bPaths=[
        'btob/website_configuration/company_active',
        'btob/website_configuration/negotiablequote_active',
        'btob/website_configuration/quickorder_active',
        'btob/website_configuration/requisitionlist_active',
        'btob/website_configuration/sharedcatalog_active',
        'btob/website_configuration/purchaseorder_enabled',
        'btob/website_configuration/direct_products_price_assigning',
        'btob/default_b2b_payment_methods/applicable_payment_methods',
        'btob/default_b2b_payment_methods/available_payment_methods',
        'btob/default_b2b_payment_methods/applicable_payment_methods',
        'btob/default_b2b_shipping_methods/available_shipping_methods'
    ];

    /** @var Authentication */
    private $authentication;

    /** @var ConfigurationDataProvider */
    private $configurationDataProvider;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /**
     * @param Authentication $authentication
     * @param ConfigurationDataProvider $configurationDataProvider
     * @param ScopeConfigInterface $scopeConfig
     * @return void
     */
    public function __construct(
        Authentication $authentication,
        ConfigurationDataProvider $configurationDataProvider,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->authentication = $authentication;
        $this->configurationDataProvider = $configurationDataProvider;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get data store configuration settings
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

        if (!empty($args['additionalSettings'])) {
            $paths = array_merge($args['additionalSettings'], $this->defaultPaths);
        } else {
            $paths = $this->defaultPaths;
        }

        if (empty($args['suppressB2BSettings'])) {
            $paths = array_merge($paths, $this->b2bPaths);
        } else {
            if (!$args['suppressB2BSettings']) {
                $paths = array_merge($paths, $this->b2bPaths);
            }
        }

        //this->scopeConfig->getValue($path, $scopeType, $scopeCode);

        $settingsData = $this->getSettingsData($paths, $context->getExtensionAttributes()->getStore()->getCode());

        return [
            'items' => $settingsData,
        ];
    }

    /**
     * Get settings data
     *
     * @param array $paths
     * @param string $storeCode
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getSettingsData(array $paths, $storeCode): array
    {
        $settingsData = [];
        foreach ($paths as $path) {
            $settingsData[] = $this->configurationDataProvider->getSettingsData($path, $storeCode);
        }
        return array_filter($settingsData);
    }
}
