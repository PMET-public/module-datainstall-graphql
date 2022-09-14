<?php
/**
 * Copyright © Adobe, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataInstall;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstall\Model\Queue\ScheduleBulk;

/**
 * CMS blocks field resolver, used for GraphQL request processing
 */
class ScheduleJob implements ResolverInterface
{
    /**
     * @var ScheduleBulk
     */
    private $scheduleBulk;

    /**
     *
     * @param ScheduleBulk $scheduleBulk
     */
    public function __construct(
        ScheduleBulk $scheduleBulk
    ) {
        $this->scheduleBulk = $scheduleBulk;
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
        $operation['load']=$jobArgs['load'];
        $operation['fileOrder']=$jobArgs['files'];
        $operation['reload']=$jobArgs['reload'];
        $operation['host']=$jobArgs['host'];
        $jobId = $this->scheduleBulk->execute([$operation]);
        return  ['job_id'=>$jobId];
    }
}
