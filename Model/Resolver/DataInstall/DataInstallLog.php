<?php
/**
 * Copyright © Adobe, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataInstall;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\DataInstallLog as LogDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class DataInstallLog implements ResolverInterface
{
    /**
     * @var LogDataProvider
     */
    private $logDataProvider;

    /**
     * @param LogDataProvider $logDataProvider
     */
    public function __construct(
        LogDataProvider $logDataProvider
    ) {
        $this->logDataProvider = $logDataProvider;
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
            $logData = $this->logDataProvider->getLogByJobId($args['jobId']);
        } elseif (!empty($args['datapack'])) {
            $logData = $this->logDataProvider->getLogByDatpack($args['datapack']);
        }
        return [
             'log_records' => $logData,
        ];
    }
}
