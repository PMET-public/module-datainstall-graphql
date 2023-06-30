<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\Widget as WidgetDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use function is_numeric;

class Widgets implements ResolverInterface
{
    /** @var WidgetDataProvider */
    private $widgetDataProvider;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param WidgetDataProvider $widgetDataProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        WidgetDataProvider $widgetDataProvider,
        Authentication $authentication
    ) {
        $this->widgetDataProvider = $widgetDataProvider;
        $this->authentication = $authentication;
    }

    /**
     * Return Widget Data
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

        $widgetIdentifiers = $this->getWidgetIdentifiers($args);
        $widgetsData = $this->getWidgetsData($widgetIdentifiers);

        return [
            'items' => $widgetsData,
        ];
    }

    /**
     * Get widget identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getWidgetIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Widgets should be specified'));
        }
        if ($args['identifiers'][0] == '') {
            $args['identifiers'] = $this->widgetDataProvider->getAllWidgetIds();
        }
        return $args['identifiers'];
    }

    /**
     * Get widgets data
     *
     * @param array $widgetIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getWidgetsData(array $widgetIdentifiers): array
    {
        $widgetsData = [];
        foreach ($widgetIdentifiers as $widgetIdentifier) {
            try {
                if (!is_numeric($widgetIdentifier)) {
                    $widgetsData[$widgetIdentifier] = $this->widgetDataProvider
                        ->getWidgetDataByName($widgetIdentifier);
                } else {
                    $widgetsData[$widgetIdentifier] = $this->widgetDataProvider
                        ->getWidgetDataById((int)$widgetIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $widgetsData[$widgetIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $widgetsData;
    }
}
