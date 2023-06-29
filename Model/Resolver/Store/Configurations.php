<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
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
