<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory as WidgetCollection;
use Magento\Widget\Model\Widget\Instance as WidgetInstance;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use MagentoEse\DataInstallGraphQl\Model\Converter\RequiredDataInterfaceFactory;
use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\Widget;

class WidgetRequiredData implements ResolverInterface
{
    /** @var WidgetCollection */
    protected $widgetCollection;

    /** @var WidgetInstance */
    protected $widgetInstance;

    /** @var Converter */
    protected $converter;

    /** @var Authentication */
    protected $authentication;

    /** @var RequiredDataInterfaceFactory */
    protected $requiredDataFactory;
    
    /**
     *
     * @param WidgetCollection $widgetCollection
     * @param WidgetInstance $widgetInstance
     * @param Converter $converter
     * @param Authentication $authentication
     * @param RequiredDataInterfaceFactory $requiredDataFactory
     * @return void
     */
    public function __construct(
        WidgetCollection $widgetCollection,
        WidgetInstance $widgetInstance,
        Converter $converter,
        Authentication $authentication,
        RequiredDataInterfaceFactory $requiredDataFactory
    ) {
        $this->widgetCollection = $widgetCollection;
        $this->widgetInstance = $widgetInstance;
        $this->converter = $converter;
        $this->authentication = $authentication;
        $this->requiredDataFactory = $requiredDataFactory;
    }
    
    /**
     * Returns other elements required by the widget
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
        if (!empty($value['widget_id'])) {
            $requiredData = $this->requiredDataFactory->create();
            $widgetResults = $this->widgetCollection->create()->
            addFieldToFilter('instance_id', $value['widget_id'])->getItems();
            $widget = current($widgetResults);
            $widgetId = $widget->getId();
            //loading to get plugin to fire to populate page_groups data
            $widget = $this->widgetInstance->load($widgetId);
            //Only supporting single layout type per widget
            $pageGroups = $widget->getDataByKey('page_groups')[0];
            
            //get entities values for substitution
            $entities = '';
            if ($pageGroups['page_for']=='specific') {
                switch (true) {
                    case strpos($pageGroups['page_group'], 'products'):
                        $entities = $this->getProductIdTags($pageGroups['entities']);
                        break;
                    case strpos($pageGroups['page_group'], 'categories'):
                        $entities = $this->getCategoryIdTags($pageGroups['entities']);
                        break;
                    case strpos($pageGroups['page_group'], 'page'):
                        $entities = $this->getPageIdTags($pageGroups['entities']);
                        break;
                }
            }
            $f = json_encode($widget->getWidgetParameters());
            $returnData = $requiredData->
            getRequiredData($entities." ".json_encode($widget->getWidgetParameters()));
            return $returnData;
        } else {
            return null;
        }
    }

     /**
      * Get tags to replace category ids
      *
      * @param string $categoryIds
      * @return string
      */
    private function getCategoryIdTags($categoryIds)
    {
        $tagArray = [];
        $categoryIds = explode(',', $categoryIds);
        foreach ($categoryIds as $categoryId) {
            $tagArray[] = "id_path='category/".$categoryId."'";
        }
        return implode(' ', $tagArray);
    }

    /**
     * Get tags to replace product ids
     *
     * @param string $productIds
     * @return string
     */
    private function getProductIdTags($productIds)
    {
        $tagArray = [];
        $productIds = explode(',', $productIds);
        foreach ($productIds as $productId) {
            $tagArray[] = "id_path='product/".$productId."'";
        }
        return implode(' ', $tagArray);
    }

    /**
     * Get tags to replace page ids
     *
     * @param string $pageIds
     * @return string
     */
    private function getPageIdTags($pageIds)
    {
        $tagArray = [];
        $pageIds = explode(',', $pageIds);
        foreach ($pageIds as $pageId) {
            $tagArray[] = "page_id='".$pageId."'";
        }
        return implode(' ', $tagArray);
    }
}
