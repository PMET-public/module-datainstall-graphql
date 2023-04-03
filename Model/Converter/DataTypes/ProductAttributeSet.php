<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Eav\Api\AttributeSetRepositoryInterface;

class ProductAttributeSet
{
    /** @var string */
    protected $tokenStart = '{{attributeset name="';

    /** @var string */
    protected $tokenEnd = '"}}';

    /** @var array */
    protected $regexToSearch = [
        ['regex'=> '/"attribute":"attribute_set_id","operator":"!=","value":"([0-9]+)"/',
        'substring'=> '"attribute":"attribute_set_id","operator":"!=","value":"'],
        ['regex'=> '/"attribute":"attribute_set_id","operator":"==","value":"([0-9]+)"/',
        'substring'=> '"attribute":"attribute_set_id","operator":"==","value":"'],
        ['regex'=> '/"attribute":"attribute_set_id","operator":"","value":"([0-9]+)"/',
        'substring'=> '"attribute":"attribute_set_id","operator":"","value":"'],
        ['regex'=> '/"attribute":"attribute_set_id","operator":"\(\)","value":"([0-9]+)"/',
        'substring'=> '"attribute":"attribute_set_id","operator":"()","value":"'],
        ['regex'=> '/"attribute":"attribute_set_id","operator":"!\(\)","value":"([0-9]+)"/',
        'substring'=> '"attribute":"attribute_set_id","operator":"!()","value":"']
    ];
    /** @var AttributeSetRepositoryInterface */
    protected $attributeSetRepository;

    /**
     * Constructor
     *
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     */
    public function __construct(
        AttributeSetRepositoryInterface $attributeSetRepository
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * Replace atribute set ids with tokens
     *
     * @param string $content
     * @return string
     */
    public function replaceAttributeSetIds($content)
    {
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesSetId, PREG_SET_ORDER);
            foreach ($matchesSetId as $match) {
                $idToReplace = $match[1];
                if ($idToReplace) {
                    //ids may be a list
                    $setIds = explode(",", $idToReplace);
                    $replacementString = '';
                    foreach ($setIds as $setId) {
                        $set = $this->attributeSetRepository->get($setId);
                        $setName = $set->getAttributeSetName();
                        $replacementString.= $this->tokenStart.$setName.$this->tokenEnd;
                    }
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
}
