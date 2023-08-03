<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Banner\Model\ResourceModel\Banner\CollectionFactory as BannerCollection;

class DynamicBlock
{
    /** @var string */
    protected $tokenStart = '{{dynamicblock name="';
    
    /** @var string */
    protected $tokenEnd = '"}}';
    
    /** @var array */
    protected $regexToSearch = [
        ['regex'=> '/"banner_ids":"([0-9,]+)"/',
        'substring'=> '"banner_ids":"'],
        ['regex'=> '/banner_ids="([0-9,]+)"/',
        'substring'=> 'banner_ids="']
    ];
    
    /** @var BannerCollection */
    protected $bannerCollection;

    /**
     * Constructor
     *
     * @param BannerCollection $bannerCollection
     */
    public function __construct(
        BannerCollection $bannerCollection
    ) {
        $this->bannerCollection = $bannerCollection;
    }

    /**
     * Replace dynamic block ids with tokens
     *
     * @param string $content
     * @return string
     */
    public function replaceDynamicBlockIds($content)
    {
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesBannerId, PREG_SET_ORDER);
            foreach ($matchesBannerId as $match) {
                $idToReplace = $match[1];
                if ($idToReplace) {
                    //id may be a list
                    $bannerIds = explode(",", $idToReplace);
                    $replacementString = '';
                    foreach ($bannerIds as $bannerId) {
                        $bannerResults = $this->bannerCollection->create()
                        ->addFieldToFilter('banner_id', [$bannerId])->getItems();
                        $banner = current($bannerResults);
                        $name = $banner->getName();
                        $replacementString.= $this->tokenStart.$name.$this->tokenEnd.",";
                    }
                    $replacementString = $this->strLreplace(',', "", $replacementString);
                    $content = str_replace(
                        $search['substring'].$idToReplace,
                        $search['substring'].$replacementString,
                        $content
                    );
                }
            }
        }
        return $content;
    }

    /**
     * Get required dynamic blocks
     *
     * @param string $content
     * @param string $type
     * @return array
     */
    public function getRequiredDynamicBlocks($content, $type): array
    {
        $requiredData = [];
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesBannerId, PREG_SET_ORDER);
            foreach ($matchesBannerId as $match) {
                $requiredBanner = [];
                $idRequired = $match[1];
                if ($idRequired) {
                    //id may be a list
                    $bannerIds = explode(",", $idRequired);

                    foreach ($bannerIds as $bannerId) {
                        $bannerResults = $this->bannerCollection->create()
                        ->addFieldToFilter('banner_id', [$bannerId])->getItems();
                        $banner = current($bannerResults);
                        $requiredBanner['name'] = $banner->getName();
                        $requiredBanner['id'] = $banner->getId();
                        $requiredBanner['type'] = $type;
                        $requiredBanner['identifier'] = $banner->getIdentifier();
                        $requiredData[] = $requiredBanner;
                    }
                }
            }
        }
        return $requiredData;
    }
    /**
     * StrLreplace
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */

    private function strLreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);
    
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }
    
        return $subject;
    }
}
