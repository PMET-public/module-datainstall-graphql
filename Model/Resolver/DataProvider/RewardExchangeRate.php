<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\Reward\Model\ResourceModel\Reward\Rate\CollectionFactory as RateCollection;
use Magento\Reward\Model\Reward\Rate;
use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\CustomerGroup as CustomerGroupDataProvider;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class RewardExchangeRate
{
    /**
     * @var RateCollection
     */
    private $rateCollection;

    /**
     * @var CustomerGroupDataProvider
     */
    private $customerGroupDataProvider;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param RateCollection $rateCollection
     * @param CustomerGroupDataProvider $customerGroupDataProvider
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RateCollection $rateCollection,
        CustomerGroupDataProvider $customerGroupDataProvider,
        WebsiteRepositoryInterface $websiteRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->rateCollection = $rateCollection;
        $this->customerGroupDataProvider = $customerGroupDataProvider;
        $this->websiteRepository = $websiteRepository;
        $this->storeManager = $storeManager;
    }

     /**
      * Get rate by id
      *
      * @param int $exchangeRateId
      * @return array
      * @throws NoSuchEntityException
      */
    public function getRateDataById(int $exchangeRateId): array
    {
        $rateData = $this->fetchRateData($exchangeRateId);

        return $rateData;
    }

    /**
     * Get all rate ids
     *
     * @return array
     */
    public function getAllExchangeRateIds(): array
    {
        $rateQuery = $this->rateCollection->create();
        $rateResults = $rateQuery->getItems();
        $rateIds = [];
        foreach ($rateResults as $rate) {
             $rateIds[] = $rate->getRateId();
        }
        return $rateIds;
    }

    /**
     * Fetch group data by either id or field
     *
     * @param mixed $identifier
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchRateData($identifier): array
    {
         /** @var Rate $rate */
         $rate = $this->rateCollection->create()
         ->addFieldToFilter('rate_id', ['eq' => $identifier])->getFirstItem();
        if (empty($rate)) {
            throw new NoSuchEntityException(
                __('The Reward Exchange Rate with ID "%1" doesn\'t exist.', $identifier)
            );
        }

        $rateText = $rate->getRateText(
            $rate->getDirection(),
            $rate->getPoints(),
            $rate->getCurrencyAmount(),
            $this->storeManager->getWebsite($rate->getWebsiteId())->getBaseCurrencyCode()
        );
        return [
            'site_code' => $this->getWebsiteCode($rate->getWebsiteId()),
            'customer_group' => $this->customerGroupDataProvider
            ->getGroupDataById((int)$rate->getCustomerGroupId())['name'],
            'direction' => $this->getExchangeDirection($rate->getDirection()),
            'points' => $rate->getPoints(),
            'currency_amount' => $rate->getCurrencyAmount(),
            'rate_id' => $rate->getRateId(),
            'rate_text' => $rateText
        ];
    }

    /**
     * Get website code by id
     *
     * @param int $siteId
     * @return string
     */
    private function getWebsiteCode($siteId)
    {
        $site = $this->websiteRepository->getById($siteId);
        return $site->getCode();
    }

    /**
     * Get exchange direction by id
     *
     * @param int $directionId
     * @return string
     */
    private function getExchangeDirection($directionId)
    {
        return $directionId == Rate::RATE_EXCHANGE_DIRECTION_TO_CURRENCY ? 'points_to_currency':'currency_to_points';
    }
}
