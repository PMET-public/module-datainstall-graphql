<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataInstall;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\DataInstallLog as LogDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class DataInstallDataPacks implements ResolverInterface
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

        $logData = $this->logDataProvider->getInstalledDataPacks();
        
        return [
             'datapacks' => $logData,
        ];
    }
}
