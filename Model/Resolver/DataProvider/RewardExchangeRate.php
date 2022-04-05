<?php
/**
 * Copyright © Adobe, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\Reward\Model\ResourceModel\Reward\Rate\CollectionFactory as RateCollection;
use Magento\Reward\Model\Reward\Rate;
use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\CustomerGroup as CustomerGroupDataProvider;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer Segment data provider
 */
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
     * @param RateCollection $rateResourceModel
     * @param CustomerGroupDataProvider $customerGroupDataProvider
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        RateCollection $rateCollection,
        CustomerGroupDataProvider $customerGroupDataProvider,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->rateCollection = $rateCollection;
        $this->customerGroupDataProvider = $customerGroupDataProvider;
        $this->websiteRepository = $websiteRepository;
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
 
        return [
            'site_code' => $this->getWebsiteCode($rate->getWebsiteId()),
            'customer_group' => $this->customerGroupDataProvider
            ->getGroupDataById((int)$rate->getCustomerGroupId())['name'],
            'direction' => $this->getExchangeDirection($rate->getDirection()),
            'points' => $rate->getPoints(),
            'currency_amount' => $rate->getCurrencyAmount()
        ];
    }

    /**
     * @param int $siteId
     * @return string
     */
    private function getWebsiteCode($siteId)
    {
        $site = $this->websiteRepository->getById($siteId);
        return $site->getCode();
    }

    /**
     * @param int $directionId
     * @return string
     */
    private function getExchangeDirection($directionId)
    {
        return $directionId == Rate::RATE_EXCHANGE_DIRECTION_TO_CURRENCY ? 'points_to_currency':'currency_to_points';
    }
}
