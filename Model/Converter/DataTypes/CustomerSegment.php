<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\CustomerSegment\Model\ResourceModel\Segment\CollectionFactory as SegmentCollection;

class CustomerSegment
{
    /** @var string */
    protected $tokenStart = '{{segment name="';

    /** @var string */    
    protected $tokenEnd = '"}}';
    
    // phpcs:ignoreFile Generic.Files.LineLength.TooLong
    protected $regexToSearch = [
        ['regex'=>'/{"type":"Magento\\\\\\\\CustomerSegment\\\\\\\\Model\\\\\\\\Segment\\\\\\\\Condition\\\\\\\\Segment","attribute":false,"operator":"==","value":"([0-9,]+)"/',
        'substring'=> 'Segment","attribute":false,"operator":"==","value":"'],
        ['regex'=>'/{"type":"Magento\\\\\\\\CustomerSegment\\\\\\\\Model\\\\\\\\Segment\\\\\\\\Condition\\\\\\\\Segment","attribute":false,"operator":"!=","value":"([0-9,]+)"/',
        'substring'=> 'Segment","attribute":false,"operator":"!=","value":"'],
        ['regex'=>'/{"type":"Magento\\\\\\\\CustomerSegment\\\\\\\\Model\\\\\\\\Segment\\\\\\\\Condition\\\\\\\\Segment","attribute":false,"operator":"","value":"([0-9,]+)"/',
        'substring'=> 'Segment","attribute":false,"operator":"","value":"'],
        ['regex'=>'/{"type":"Magento\\\\\\\\CustomerSegment\\\\\\\\Model\\\\\\\\Segment\\\\\\\\Condition\\\\\\\\Segment","attribute":false,"operator":"\(\)","value":"([0-9,]+)"/',
        'substring'=> 'Segment","attribute":false,"operator":"","value":"'],
        ['regex'=>'/{"type":"Magento\\\\\\\\CustomerSegment\\\\\\\\Model\\\\\\\\Segment\\\\\\\\Condition\\\\\\\\Segment","attribute":false,"operator":"!\(\)","value":"([0-9,]+)"/',
        'substring'=> 'Segment","attribute":false,"operator":"","value":"'],
        ['regex'=>'/"segment_id=([0-9,]+)"/',
        'substring'=> '"segment_id=']
    ];
    /** @var SegmentCollection */
    protected $segmentCollection;

    /**
     * Constructor
     * 
     * @param SegmentCollection $segmentCollection
     */
    public function __construct(
        SegmentCollection $segmentCollection
    ) {
        $this->segmentCollection = $segmentCollection;
    }

    /**
     * Replace segment ids with tokens
     * 
     * @param string $content
     * @return string
     */
    public function replaceSegmentIds($content)
    {
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesSegmentId, PREG_SET_ORDER);
            foreach ($matchesSegmentId as $match) {
                $idToReplace = $match[1];
                if ($idToReplace) {
                    //ids may be a list
                    $segmentIds = explode(",", $idToReplace);
                    $replacementString = '';
                    foreach ($segmentIds as $segmentId) {
                        $segmentResults = $this->segmentCollection->create()
                        ->addFieldToFilter('segment_id', [$segmentId])->getItems();
                        $segment = current($segmentResults);
                        $segmentName = $segment->getName();
                        $replacementString.= $this->tokenStart.$segmentName.$this->tokenEnd;
                    }
                    $content = str_replace($search['substring'].$idToReplace, $replacementString, $content);
                }
            }
        }
        return $content;
    }

    public function getRequiredSegments($content,$type)
    {
        $requiredData = [];
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesSegmentId, PREG_SET_ORDER);
            foreach ($matchesSegmentId as $match) {
                $requiredSegment = [];
                $idRequired = $match[1];
                if ($idRequired) {
                    //ids may be a list
                    $segmentIds = explode(",", $idRequired);
                    foreach ($segmentIds as $segmentId) {
                        $segmentResults = $this->segmentCollection->create()
                        ->addFieldToFilter('segment_id', [$segmentId])->getItems();
                        $segment = current($segmentResults);
                        $requiredSegment['name'] = $segment->getName();
                        $requiredSegment['id'] = $segment->getId();
                        $requiredSegment['type'] = $type;
                        $requiredData[] = $requiredSegment;
                    }
                }
            }    
        }
        return $requiredData;
    }
}
