<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class MsiSource
{
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository
    ) {
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * Get source by code
     *
     * @param string $sourcecode
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSourcebyCode(string $sourcecode): array
    {
        $sourceData = $this->fetchSourceData($sourcecode);

        return $sourceData;
    }

    /**
     * Fetch group data by either id or field
     *
     * @param mixed $identifier
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchSourceData($identifier): array
    {
        $source = $this->sourceRepository->get($identifier);
        if (empty($source)) {
            throw new NoSuchEntityException(
                __('The Msi Source with code" "%1" doesn\'t exist.', $identifier)
            );
        }
        /** @var SourceInterface $source */

        return [
            'source_code' => $source->getSourceCode(),
            'name' => $source->getName(),
            'enabled' => $source->isEnabled(),
            'description' => $source->getDescription(),
            'latitude' => $source->getLatitude(),
            'longitude' => $source->getLongitude(),
            'region_id' => $source->getRegionId(),
            'country_id' => $source->getCountryId(),
            'city' => $source->getCity(),
            'street' => $source->getStreet(),
            'postcode' => $source->getPostcode(),
            'contact_name' => $source->getContactName(),
            'email' => $source->getEmail(),
            'phone' => $source->getPhone(),
            'fax' => $source->getFax(),
            'use_default_carrier_config' => $source->isUseDefaultCarrierConfig(),
            'is_pickup_location_active' => $source->getIsPickupLocationActive(),
            'frontend_name' => $source->getFrontendName(),
            'frontend_description' => $source->getFrontendDescription(),
        ];
    }
}
