<?php
namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Banner\Model\ResourceModel\Banner\CollectionFactory as BannerCollection;

class DynamicBlock
{
    protected $tokenStart = '{{dynamicblock name="';
    protected $tokenEnd = '"}}';
    protected $regexToSearch = [
        ['regex'=> '/"banner_ids":"([0-9]+)"/',
        'substring'=> '"banner_ids":"']
    ];
    /** @var BannerCollection */
    protected $bannerCollection;

    /**
     * @param BannerCollection $bannerCollection
     */
    public function __construct(
        BannerCollection $bannerCollection
    ) {
        $this->bannerCollection = $bannerCollection;
    }

    /**
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
                        $replacementString.= $this->tokenStart.$name.$this->tokenEnd;
                    }
                    $content = str_replace($search['substring'].$idToReplace, $replacementString, $content);
                }
            }
        }
        return $content;
    }
}
