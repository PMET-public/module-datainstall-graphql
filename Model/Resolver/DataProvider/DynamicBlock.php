<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\Banner\Model\ResourceModel\Banner\CollectionFactory as BannerCollection;
use Magento\Banner\Model\ResourceModel\Banner as BannerResource;
use Magento\Banner\Model\Config;
use Magento\BannerCustomerSegment\Model\ResourceModel\BannerSegmentLink;
use Magento\CustomerSegment\Model\ResourceModel\Segment\CollectionFactory as SegmentCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;

class DynamicBlock
{
    /**
     * @var BannerCollection
     */
    private $bannerCollection;

    /**
     * @var BannerResource
     */
    private $bannerResource;

    /**
     * @var BannerSegmentLink
     */
    private $bannerSegmentLink;

    /**
     * @var Config
     */
    private $bannerConfig;

    /**
     * @var SegmentCollection
     */
    private $segmentCollection;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @param BannerCollection $bannerCollection
     * @param BannerResource $bannerResource
     * @param BannerSegmentLink $bannerSegmentLink
     * @param Config $bannerConfig
     * @param SegmentCollection $segmentCollection
     * @param Converter $converter
     */
    public function __construct(
        BannerCollection $bannerCollection,
        BannerResource $bannerResource,
        BannerSegmentLink $bannerSegmentLink,
        Config $bannerConfig,
        SegmentCollection $segmentCollection,
        Converter $converter
    ) {
        $this->bannerCollection = $bannerCollection;
        $this->bannerResource = $bannerResource;
        $this->bannerSegmentLink = $bannerSegmentLink;
        $this->bannerConfig = $bannerConfig;
        $this->segmentCollection = $segmentCollection;
        $this->converter = $converter;
    }

    /**
     * Get banner by name
     *
     * @param string $bannerName
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByDynamicBlockName(string $bannerName, int $storeId): array
    {
        $bannerData = $this->fetchBannerData($bannerName, 'name', $storeId);

        return $bannerData;
    }

    /**
     * Get banner by id
     *
     * @param int $bannerId
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByDynamicBlockId(int $bannerId, int $storeId): array
    {
        $bannerData = $this->fetchBannerData($bannerId, 'banner_id', $storeId);

        return $bannerData;
    }

    /**
     * Fetch banner data by field
     *
     * @param mixed $identifier
     * @param string $field
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchBannerData($identifier, string $field, int $storeId): array
    {
        $bannerResults = $this->bannerCollection->create()->addFieldToFilter($field, [$identifier])->getItems();
        $banner = current($bannerResults);
        $bannerId = $banner->getBannerId();
        //get content and segments
        $bannerContent = $this->getStoreContent($bannerId, $storeId);
        $bannerSegmentIds = $this->bannerSegmentLink->loadBannerSegments($bannerId);
        if (empty($bannerSegmentIds)) {
            $segmentNames = 'all';
        } else {
            $segmentNames = $this->getSegmentNames($bannerSegmentIds);
        }
        
        if (empty($banner)) {
            throw new NoSuchEntityException(
                __('The banner with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }
        $banners = [
                'name' => $banner->getName(),
                'segments' => $segmentNames,
                'type' => implode(",", $banner->getTypes()),
                'banner_content' => $this->converter->convertContent($bannerContent),
                'is_enabled' => $banner->getIsEnabled(),
                'banner_id' => $banner->getBannerId(),
                'ui_type' => $this->getUiBannerTypes($banner->getTypes())
                ];

        return $banners;
    }

    /**
     * Get banner types names
     *
     * @param array $savedTypes
     * @return string
     */
    private function getUiBannerTypes(array $savedTypes) :string
    {
        $types = [];
        $bannerTypes = $this->bannerConfig->getTypes();
        //iterate over types defined for banner
        foreach ($savedTypes as $savedType) {
            //iterate over types defined in config
            foreach ($bannerTypes as $type => $value) {
                //if type matches, add to array
                if ($savedType == $type) {
                    $types[] = $bannerTypes[$type]->getText();
                }
            }
        }
        return implode(",", $types);
    }

    /**
     * Get all banner ids
     *
     * @return array
     */
    public function getAllBannerIds(): array
    {
        $bannerQuery = $this->bannerCollection->create();
        $bannerResults = $bannerQuery->getItems();
        $bannerIds = [];
        foreach ($bannerResults as $banner) {
             $bannerIds[] = $banner->getBannerId();
        }
        return $bannerIds;
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
     * Get segment names by ids
     *
     * @param array $segmentIds
     * @return string
     */
    private function getSegmentNames(array $segmentIds)
    {
        $segmentResults = $this->segmentCollection->create()->addFieldToFilter('segment_id', [$segmentIds])->getItems();
        $segmentNames = [];
        foreach ($segmentResults as $segmentResult) {
            $segmentNames[] = $segmentResult->getName();
        }
        return implode(",", $segmentNames);
    }
}
