<?php
/**
 * Copyright © Adobe, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataInstall;

use Magento\Framework\Bulk\BulkStatusInterface;
use Magento\Framework\Bulk\BulkSummaryInterface as BulkSummary;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class DataInstallJobStatus implements ResolverInterface
{
    /**
     * @var BulkStatusInterface
     */
    private $bulkStatus;

    /**
     * @param BulkStatusInterface $bulkStatus
     */
    public function __construct(
        BulkStatusInterface $bulkStatus
    ) {
        $this->bulkStatus = $bulkStatus;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
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
