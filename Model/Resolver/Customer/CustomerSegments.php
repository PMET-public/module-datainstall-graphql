<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Customer;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\CustomerSegment as CustomerSegmentDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use function is_numeric;

class CustomerSegments implements ResolverInterface
{
    /** @var CustomerSegmentDataProvider */
    private $customerSegmentDataProvider;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param CustomerSegmentDataProvider $customerSegmentDataProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        CustomerSegmentDataProvider $customerSegmentDataProvider,
        Authentication $authentication
    ) {
        $this->customerSegmentDataProvider = $customerSegmentDataProvider;
        $this->authentication = $authentication;
    }

    /**
     * Get customer segments
     *
     * @param Field $field
     * @param Context $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     * @return mixed
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

        $segmentIdentifiers = $this->getSegmentIdentifiers($args);
        $segmentData = $this->getSegmentsData($segmentIdentifiers);

        return [
            'items' => $segmentData,
        ];
    }

    /**
     * Get segment identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getSegmentIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Customer Segments should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get segment data
     *
     * @param array $segmentIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getSegmentsData(array $segmentIdentifiers): array
    {
        $segmentsData = [];
        foreach ($segmentIdentifiers as $segmentIdentifier) {
            try {
                if (!is_numeric($segmentIdentifier)) {
                    $segmentsData[$segmentIdentifier] = $this->customerSegmentDataProvider
                        ->getSegmentDataByName($segmentIdentifier);
                } else {
                    $segmentsData[$segmentIdentifier] = $this->customerSegmentDataProvider
                        ->getSegmentDataById((int)$segmentIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $segmentsData[$segmentIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $segmentsData;
    }
}
