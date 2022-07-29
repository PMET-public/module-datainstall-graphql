<?php
/**
 * Copyright © Adobe, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory as WidgetCollection;
use Magento\Widget\Model\Widget\Instance as WidgetInstance;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;

/**
 * Upsell data provider
 */
class Widget
{
    /**
     * @var WidgetCollection
     */
    private $widgetCollection;

    /**
     * @var WidgetInstance
     */
    private $widgetInstance;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @param WidgetCollection $widgetCollection
     * @param WidgetInstance $widgetInstance
     * @param StoreRepositoryInterface $storeRepository
     * @param Converter $converter
     */
    public function __construct(
        WidgetCollection $widgetCollection,
        WidgetInstance $widgetInstance,
        StoreRepositoryInterface $storeRepository,
        Converter $converter
    ) {
        $this->widgetCollection = $widgetCollection;
        $this->widgetInstance = $widgetInstance;
        $this->storeRepository = $storeRepository;
        $this->converter = $converter;
    }

    /**
     * Get upsell by name
     *
     * @param string $widgetName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getWidgetDataByName(string $widgetName): array
    {
        $widgetData = $this->fetchWidgetData($widgetName, 'title');

        return $widgetData;
    }

    /**
     * Get group data by id
     *
     * @param int $widgetId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getWidgetDataById(int $widgetId): array
    {
        $widgetData = $this->fetchWidgetData($widgetId, 'instance_id');

        return $widgetData;
    }

    /**
     * Fetch upsell data by either id or field
     *
     * @param mixed $identifier
     * @param string $field
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchWidgetData($identifier, string $field): array
    {
        $widgetResults = $this->widgetCollection->create()->addFieldToFilter($field, [$identifier])->getItems();

        if (empty($widgetResults)) {
            throw new NoSuchEntityException(
                __('The related widget with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }
        /** @var WidgetInstance $widget */
        $widget = current($widgetResults);
        $widgetId = $widget->getId();
        //loading to get plugin to fire to populate page_groups data
        $widget = $this->widgetInstance->load($widgetId);
        $pageGroups = $widget->getDataByKey('page_groups')[0];

        if (empty($widget)) {
            throw new NoSuchEntityException(
                __('The realated product rule with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }
        return [
            'title' => $widget->getTitle(),
            'theme' => $widget->getThemeId(),
            'instance_type' => $widget->getType(),
            'store_view_code' => $this->getStoreViewCodes($widget->getStoreIds()),
            'widget_parameters' => $this->converter->convertContent(json_encode($widget->getWidgetParameters())),
            'sort_order' =>  $widget->getSortOrder(),
            'page_group' =>  $pageGroups['page_group'],
            'layout_handle' =>  $pageGroups['layout_handle'],
            'block_reference' =>  $pageGroups['block_reference'],
            'page_for' =>  $pageGroups['page_for'],
            'entities' => $pageGroups['entities'],
            'page_template' =>  $pageGroups['page_template']
        ];
    }
    /**
     * Get view codes from ids
     *
     * @param array $storeViewIds
     * @return string
     */
    private function getStoreViewCodes($storeViewIds)
    {
        $storeCodes = [];
        foreach ($storeViewIds as $storeViewId) {
            $store = $this->storeRepository->getById($storeViewId);
            $storeCodes[] = $store->getCode();
        }
        return implode(",", $storeCodes);
    }
}
