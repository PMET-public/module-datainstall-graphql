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
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class DataInstallLog implements ResolverInterface
{
    /**
     * @var LogDataProvider
     */
    private $logDataProvider;

    /** @var Authentication */
    protected $authentication;

   /**
    *
    * @param LogDataProvider $logDataProvider
    * @param Authentication $authentication
    * @return void
    */
    public function __construct(
        LogDataProvider $logDataProvider,
        Authentication $authentication
    ) {
        $this->logDataProvider = $logDataProvider;
        $this->authentication = $authentication;
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
        $this->authentication->authorize();

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
