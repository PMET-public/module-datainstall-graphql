<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\CustomerSegment\Model\Segment;
use Magento\CustomerSegment\Model\ResourceModel\Segment\CollectionFactory as SegmentCollection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Customer Segment data provider
 */
class CustomerSegment
{
    /**
     * @var SegmentCollection
     */
    private $segmentCollection;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @param SegmentCollection $segmentCollection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        SegmentCollection $segmentCollection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->segmentCollection = $segmentCollection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * Get segment data by identifier
     *
     * @param string $segmentIdentifier
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSegmentDataByName(string $segmentName): array
    {
        $segmentData = $this->fetchsegmentData($segmentName, 'name');

        return $segmentData;
    }

    /**
     * Get block data by block_id
     *
     * @param int $blockId
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSegmentDataById(int $segmentId): array
    {
        $segmentData = $this->fetchsegmentData($segmentId, 'segment_id');

        return $segmentData;
    }

    /**
     * Fetch segment data by either id or name field
     *
     * @param mixed $identifier
     * @param string $field
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchsegmentData($identifier, string $field): array
    {
        $segmentResults = $this->segmentCollection->create()->addFieldToFilter($field, [$identifier])->getItems();

        if (empty($segmentResults)) {
            throw new NoSuchEntityException(
                __('The segment with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }
        /** @var Segment $segment */
        $segment = current($segmentResults);

        return [
            'name' => $segment->getName(),
            'site_code' => $this->getWebsiteCodes($segment->getWebsiteIds()),
            'description' => $segment->getDescription(),
            'apply_to' => $segment->getApplyTo(),
            'conditions_serialized' => $segment->getConditionsSerialized()
        ];
    }

    private function getWebsiteCodes($siteIds)
    {
        $siteCodes = [];
        foreach ($siteIds as $siteId) {
            $site = $this->websiteRepository->getById($siteId);
            $siteCodes[] = $site->getCode();
        }
        return implode(",", $siteCodes);
    }
}
