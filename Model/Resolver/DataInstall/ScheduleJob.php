<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataInstall;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstall\Api\Data\DataPackInterfaceFactory;
use MagentoEse\DataInstall\Api\Data\InstallerJobInterfaceFactory;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class ScheduleJob implements ResolverInterface
{
    /** @var DataPackInterfaceFactory */
    protected $dataPackInterface;

    /** @var InstallerJobInterfaceFactory */
    protected $installerJobInterface;

    /** @var Authentication */
    protected $authentication;
    
    /**
     *
     * @param DataPackInterfaceFactory $dataPackInterface
     * @param InstallerJobInterfaceFactory $installerJobInterface
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        DataPackInterfaceFactory $dataPackInterface,
        InstallerJobInterfaceFactory $installerJobInterface,
        Authentication $authentication
    ) {
        $this->dataPackInterface = $dataPackInterface;
        $this->installerJobInterface = $installerJobInterface;
        $this->authentication = $authentication;
    }
    
    /**
     * Reslover for createDataInstallerJob
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

        $jobArgs = $args['input'];
        if (empty($jobArgs['datapack'])) {
            throw new GraphQlInputException(__('"datapack" is required'));
        }
         /** @var DataPackInterface $dataPack */
        $dataPack = $this->dataPackInterface->create();
        $dataPack->setDataPackLocation($jobArgs['datapack']);
        
        if (!empty($jobArgs['load'])) {
            $dataPack->setLoad($jobArgs['load']);
        }

        if (!empty($jobArgs['files'])) {
            $dataPack->setFiles(explode(",", $jobArgs['files']));
        }

        if (!empty($jobArgs['host'])) {
            $dataPack->setHost($jobArgs['host']);
        }
        if (!empty($jobArgs['is_default_website'])) {
            $dataPack->setIsDefaultWebsite($jobArgs['is_default_website']);
        }
        if (!empty($jobArgs['reload'])) {
            $dataPack->setReload($jobArgs['reload']);
        }
        if (!empty($jobArgs['restrict_products_from_views'])) {
            $dataPack->setRestrictProductsFromViews($jobArgs['restrict_products_from_views']);
        }
        if (!empty($jobArgs['is_remote'])) {
            $dataPack->setIsRemote($jobArgs['is_remote']);
        }
        if (!empty($jobArgs['auth_token'])) {
            $dataPack->setAuthToken($jobArgs['auth_token']);
        } else {
            $dataPack->setAuthToken('');
        }
        if (!empty($jobArgs['override_settings'])) {
            if ($jobArgs['override_settings']) {
                $dataPack->setIsOverride(true);
                if (!empty($jobArgs['site_code'])) {
                    $dataPack->setSiteCode($jobArgs['site_code']);
                }
                if (!empty($jobArgs['site_name'])) {
                    $dataPack->setSiteName($jobArgs['site_name']);
                }
                if (!empty($jobArgs['store_code'])) {
                    $dataPack->setStoreCode($jobArgs['store_code']);
                }
                if (!empty($jobArgs['store_name'])) {
                    $dataPack->setStoreName($jobArgs['store_name']);
                }
                if (!empty($jobArgs['store_view_code'])) {
                    $dataPack->setStoreViewCode($jobArgs['store_view_code']);
                }
                if (!empty($jobArgs['store_view_name'])) {
                    $dataPack->setStoreViewName($jobArgs['store_view_name']);
                }
            } else {
                $dataPack->setIsOverride(false);
            }
        }

        if ($dataPack->getIsRemote()) {
            $dataPack->setDataPackLocation($dataPack->getRemoteDataPack(
                $dataPack->getDataPackLocation(),
                $dataPack->getAuthToken()
            ));
        }
        if ($dataPack->getIsRemote()) {
            $dataPack->unZipDataPack();
        }
        if ($dataPack->getDataPackLocation()) {
            ///schedule import
            $installerJob = $this->installerJobInterface->create();
            $jobId = $installerJob->scheduleImport($dataPack);
            return [
                'job_id' => $jobId,
            ];
        } else {
            throw new GraphQlInputException(__('Data Pack could not be unzipped. Please check file format'));
        }
    }
}
