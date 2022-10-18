<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataInstall;

use Magento\Framework\Bulk\BulkStatusInterface;
use Magento\Framework\Bulk\BulkSummaryInterface as BulkSummary;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class DataInstallJobStatus implements ResolverInterface
{
    /** @var BulkStatusInterface */
    private $bulkStatus;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param BulkStatusInterface $bulkStatus
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        BulkStatusInterface $bulkStatus,
        Authentication $authentication
    ) {
        $this->bulkStatus = $bulkStatus;
        $this->authentication = $authentication;
    }

    /**
     * Get status of data installer job
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

        if (!empty($args['jobId'])) {
            $jobStatusText="UNKNOWN";
            $jobStatus = $this->bulkStatus->getBulkStatus($args['jobId']);
            switch ($jobStatus) {
                case BulkSummary::NOT_STARTED:
                    $jobStatusText = "NOT_STARTED";
                    break;
                case BulkSummary::IN_PROGRESS:
                    $jobStatusText = "IN_PROGRESS";
                    break;
                case BulkSummary::FINISHED_SUCCESSFULLY:
                    $jobStatusText = "FINISHED_SUCCESSFULLY";
                    break;
                case BulkSummary::FINISHED_WITH_FAILURE:
                    $jobStatusText = "FINISHED_WITH_FAILURE";
                    break;
            }
        }         return [
             'job_status_text' => $jobStatusText,
             'job_status' => $jobStatus,
        ];
    }
}
