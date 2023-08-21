<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\BannerCustomerSegment\Model\ResourceModel\BannerSegmentLink;
use Magento\Banner\Model\ResourceModel\Banner as BannerResource;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use MagentoEse\DataInstallGraphQl\Model\Converter\RequiredDataInterfaceFactory;

class DynamicBlockRequiredData implements ResolverInterface
{
    /** @var BannerSegmentLink */
    protected $bannerSegmentLink;

    /** @var BannerResource */
    protected $bannerResource;

    /** @var Converter */
    protected $converter;

    /** @var Authentication */
    protected $authentication;

    /** @var RequiredDataInterfaceFactory */
    protected $requiredDataFactory;
    
    /**
     *
     * @param BannerSegmentLink $bannerSegmentLink
     * @param BannerResource $bannerResource
     * @param Converter $converter
     * @param Authentication $authentication
     * @param RequiredDataInterfaceFactory $requiredDataFactory
     * @return void
     */
    public function __construct(
        BannerSegmentLink $bannerSegmentLink,
        BannerResource $bannerResource,
        Converter $converter,
        Authentication $authentication,
        RequiredDataInterfaceFactory $requiredDataFactory
    ) {
        $this->bannerSegmentLink = $bannerSegmentLink;
        $this->bannerResource = $bannerResource;
        $this->converter = $converter;
        $this->authentication = $authentication;
        $this->requiredDataFactory = $requiredDataFactory;
    }
    
    /**
     * Returns other elements required by the block
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

        if (!empty($value['banner_id'])) {
            $requiredData = $this->requiredDataFactory->create();
            //$bannerResults = $this->bannerCollection->create()->
            //addFieldToFilter('banner_id', $value['banner_id'])->getItems();
           // $banner = current($bannerResults);
            $bannerSegmentIds = $this->bannerSegmentLink->loadBannerSegments($value['banner_id']);
            $returnData = $requiredData->
                getRequiredData($this->getSegmentIdTags($bannerSegmentIds).$this->getStoreContent(
                    $value['banner_id'],
                    $context->getExtensionAttributes()->getStore()->getId()
                ));
            return $returnData;
        } else {
            return null;
        }
    }

    /**
     * Get banner content by specific store id
     *
     * @param int $bannerId
     * @param int $storeId
     * @return string
     */
    private function getStoreContent($bannerId, $storeId)
    {
        $connection = $this->bannerResource->getConnection();
        $select = $connection->select()->from(
            ['main_table' => 'magento_banner_content'],
            'banner_content'
        )->where(
            'main_table.banner_id = ?',
            $bannerId
        )->where(
            'main_table.store_id IN (?)',
            [$storeId, 0]
        )->order(
            'main_table.store_id DESC'
        );

        $select->joinInner(
            ['banner' => $this->bannerResource->getTable('magento_banner')],
            'main_table.banner_id = banner.banner_id'
        );
        
        return $connection->fetchOne($select);
    }

    /**
     * Get tags to replace segment ids
     *
     * @param array $segmentIds
     * @return string
     */
    private function getSegmentIdTags($segmentIds)
    {
        return '"segment_id=' . implode(",", $segmentIds) . '"';
    }
}
