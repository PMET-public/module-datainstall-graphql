<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataInstall;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Setup\SampleData\FixtureManager;
use MagentoEse\DataInstall\Model\Queue\ScheduleBulk;

/**
 * CMS blocks field resolver, used for GraphQL request processing
 */
class ScheduleJob implements ResolverInterface
{
    /**
     * @var DriverInterface
     */
    private $driverInterface;

    /**
     * @var FixtureManager
     */
    private $fixtureManager;

    /**
     * @var ScheduleBulk
     */
    private $scheduleBulk;

    /**
     * @param DriverInterface $driverInterface
     * @param ScheduleBulk $scheduleBulk
     * @param FixtureManager $fixtureManager
     */
    public function __construct(
        DriverInterface $driverInterface,
        ScheduleBulk $scheduleBulk,
        FixtureManager $fixtureManager
    ) {
        $this->driverInterface = $driverInterface;
        $this->scheduleBulk = $scheduleBulk;
        $this->fixtureManager = $fixtureManager;
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
        $jobArgs = $args['input'];
        if (empty($jobArgs['datapack'])) {
            throw new GraphQlInputException(__('"datapack" is required'));
        }

        if (empty($jobArgs['load'])) {
            $jobArgs['load']='';
        }

        if (empty($jobArgs['files'])) {
            $jobArgs['files']='';
        }

        if (empty($jobArgs['host'])) {
            $jobArgs['host']='';
        }

        if (empty($jobArgs['reload'])) {
            $jobArgs['reload']=0;
        }
        $operation = [];
        $operation['fileSource'] = $jobArgs['datapack'];
        $operation['packFile']="";
        //$operation['fileSource']=$this->verticalDirectory->getAbsolutePath(self::UNZIPPED_DIR).'/'
        //phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        //.basename($fileInfo['name'], '.zip');
        //$operation['packFile']=$fileInfo['name'];
        $operation['load']=$jobArgs['load'];
        $operation['fileOrder']=$jobArgs['files'];
        $operation['reload']=$jobArgs['reload'];
        $operation['host']=$jobArgs['host'];
        $jobId = $this->scheduleBulk->execute([$operation]);
        return  ['job_id'=>$jobId];
    }

    /**
     * @param $fileLocation
     * @return string
     * @throws LocalizedException
     */
    private function getDataPath($fileLocation)
    {
        if (preg_match('/[A-Z,a-z,,0-9]+_[A-Z,a-z,0-9]+/', $fileLocation)==1) {
            $filePath = $this->fixtureManager->getFixture($fileLocation . "::");
            //if its not a valid module, the file path will just be the fixtures directory,
            //so then assume it may a relative path that looks like a module name;
            if ($filePath=='/') {
                return $fileLocation.'/';
            } else {
                return $filePath;
            }
        } else {
            //otherwise assume relative or absolute path
            //return $this->driverInterface->getRealPath($fileLocation).'/';
            return $fileLocation;
        }
    }
}
