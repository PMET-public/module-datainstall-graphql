<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\CustomerSegment\Model\Segment;
use Magento\CustomerSegment\Model\ResourceModel\Segment\CollectionFactory as SegmentCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\WebsiteRepositoryInterface;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;

class CustomerSegment
{
    /**
     * @var SegmentCollection
     */
    private $segmentCollection;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @param SegmentCollection $segmentCollection
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param Converter $converter
     */
    public function __construct(
        SegmentCollection $segmentCollection,
        WebsiteRepositoryInterface $websiteRepository,
        Converter $converter
    ) {
        $this->segmentCollection = $segmentCollection;
        $this->websiteRepository = $websiteRepository;
        $this->converter = $converter;
    }

    /**
     * Get segment data by identifier
     *
     * @param string $segmentName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSegmentDataByName(string $segmentName): array
    {
        $segmentData = $this->fetchSegmentData($segmentName, 'name');

        return $segmentData;
    }

    /**
     * Get segment data by segmentId
     *
     * @param int $segmentId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSegmentDataById(int $segmentId): array
    {
        $segmentData = $this->fetchSegmentData($segmentId, 'segment_id');

        return $segmentData;
    }

    /**
     * Fetch segment data by either id or name field
     *
     * @param mixed $identifier
     * @param string $field
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchSegmentData($identifier, string $field): array
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
            'conditions_serialized' => $this->converter->convertContent($segment->getConditionsSerialized()),
            'segment_id' => $segment->getSegmentId(),
            'is_active' => $segment->getIsActive(),
            'segment_id' => $segment->getSegmentId()
        ];
    }

    /**
     * Get all segment ids
     *
     * @return array
     */
    public function getAllSegmentIds(): array
    {
        $segmentQuery = $this->segmentCollection->create();
        $segmenteResults = $segmentQuery->getItems();
        $segmentIds = [];
        foreach ($segmenteResults as $segment) {
             $segmentIds[] = $segment->getSegmentId();
        }
        return $segmentIds;
    }
    /**
     * Get website codes by ids
     *
     * @param array $siteIds
     * @return string
     */
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
