<?php
namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class CategoryId
{
    protected $tokenStart = '{{categoryid key="';
    protected $tokenEnd = '"}}';
    protected $regexToSearch = [
        ['regex'=>"/id_path='category\/([0-9]+)'/",
        'substring'=> "id_path='category/"],
        ['regex'=>'/"attribute":"category_ids","operator":"==","value":"([0-9,]+)",/',
        'substring'=>'"attribute":"category_ids","operator":"==","value":"'],
        ['regex'=>'/"attribute":"category_ids","operator":"!=","value":"([0-9,]+)",/',
        'substring'=>'"attribute":"category_ids","operator":"!=","value":"'],
        ['regex'=>'/"attribute":"category_ids","operator":"","value":"([0-9,]+)",/',
        'substring'=>'"attribute":"category_ids","operator":"","value":"'],
        ['regex'=>'/condition_option="category_ids" condition_option_value="([0-9,]+)"/',
        'substring'=>'condition_option="category_ids" condition_option_value="'],
        ['regex'=>'/\?cat=([0-9,]+)"/',
        'substring'=>'?cat=']
    ];
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
    }

    /**
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
                    $replacementString = '';
                    foreach ($categoryIds as $categoryId) {
                        $category = $this->categoryRepository->get($categoryId);
                        $urlKey = $category->getUrlKey();
                        $replacementString.= $this->tokenStart.$urlKey.$this->tokenEnd;
                    }
                    $content = str_replace($search['substring'].$idToReplace, $replacementString, $content);
                }
            }
        }
        return $content;
    }
}
