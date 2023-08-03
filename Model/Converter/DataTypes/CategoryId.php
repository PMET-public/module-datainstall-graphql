<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CategoryId
{
    /** @var string */
    protected $tokenStart = '{{categoryid key="';
    
    /** @var string */
    protected $tokenEnd = '"}}';
    
    /** @var array */
    protected $regexToSearch = [
        ['regex'=>"/id_path='category\/([0-9]+)'/",
        'substring'=> "id_path='category/"],
        ['regex'=>'/"attribute":"category_ids","operator":"==","value":"([0-9,]+)",/',
        'substring'=>'"attribute":"category_ids","operator":"==","value":"'],
        ['regex'=>'/"attribute":"category_ids","operator":"!=","value":"([0-9,]+)",/',
        'substring'=>'"attribute":"category_ids","operator":"!=","value":"'],
        ['regex'=>'/"attribute":"category_ids","operator":"","value":"([0-9,]+)",/',
        'substring'=>'"attribute":"category_ids","operator":"","value":"'],
        ['regex'=>'/"attribute":"category_ids","operator":"\(\)","value":"([0-9,]+)",/',
        'substring'=>'"attribute":"category_ids","operator":"()","value":"'],
        ['regex'=>'/"attribute":"category_ids","operator":"!\(\)","value":"([0-9,]+)",/',
        'substring'=>'"attribute":"category_ids","operator":"!()","value":"'],
        ['regex'=>'/"attribute":"category_ids","operator":"{}}","value":"([0-9,]+)",/',
        'substring'=>'"attribute":"category_ids","operator":"{}","value":"'],
        ['regex'=>'/"attribute":"category_ids","operator":"!{}","value":"([0-9,]+)",/',
        'substring'=>'"attribute":"category_ids","operator":"!{}","value":"'],
        ['regex'=>'/condition_option="category_ids" condition_option_value="([0-9,]+)"/',
        'substring'=>'condition_option="category_ids" condition_option_value="'],
        ['regex'=>'/\?cat=([0-9,]+)"/',
        'substring'=>'?cat='],
        ['regex'=>'/Condition\|\|Product`,`attribute`:`category_ids`,`value`:`([0-9,`]+)`/',
        'substring'=>'`category_ids`,`value`:`']
    ];
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * CategoryId constructor
     *
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Replace category ids with tokens
     *
     * @param string $content
     * @return string
     */
    public function replaceCategoryIds($content)
    {
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesCategoryId, PREG_SET_ORDER);
            foreach ($matchesCategoryId as $match) {
                $idToReplace = $match[1];
                if ($idToReplace) {
                    //categoryids may be a list
                    $categoryIds = explode(",", $idToReplace);
                    $replacementArr = [];
                    foreach ($categoryIds as $categoryId) {
                        $replacementArr[]= $this->getCategoryIdTag($categoryId);
                    }
                    $content = str_replace(
                        $search['substring'].$idToReplace,
                        $search['substring'].implode(",", $replacementArr),
                        $content
                    );
                }
            }
        }
        return $content;
    }

    /**
     * Replace category ids with tokens
     *
     * @param string $content
     * @param string $type
     * @return array
     */
    public function getRequiredCategoryIds($content, $type)
    {
        $requiredData = [];
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesCategoryId, PREG_SET_ORDER);
            foreach ($matchesCategoryId as $match) {
                $requiredCategory = [];
                $idRequired = $match[1];
                if ($idRequired) {
                    //ids may be a list
                    $categoryIds = explode(",", $idRequired);
                    foreach ($categoryIds as $categoryId) {
                        $category = $this->categoryRepository->get($categoryId);
                        $requiredCategory['name'] = $category->getName();
                        $requiredCategory['id'] = $category->getId();
                        $requiredCategory['type'] = $type;
                        $requiredData[] = $requiredCategory;
                    }
                }
            }
        }
        return $requiredData;
    }

    /**
     * Get tag to replace category id
     *
     * @param int $categoryId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCategoryIdTag($categoryId)
    {
        $category = $this->categoryRepository->get($categoryId);
        $urlKey = $category->getUrlKey();
        return $this->tokenStart.$urlKey.$this->tokenEnd;
    }
}
