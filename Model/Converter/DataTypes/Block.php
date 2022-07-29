<?php
namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Cms\Api\BlockRepositoryInterface;

class Block
{
     /** @var string */
    protected $tokenStart = '{{block code="';
    
    /** @var string */
    protected $tokenEnd = '"}}';
    
    /** @var string */
    protected $regexToSearch = [
        ['regex'=> '/block_id="([0-9]+)"/',
        'substring'=> 'block_id="']
    ];
    /** @var BlockRepositoryInterface */
    protected $blockRepository;

    /**
     * Block constructor
     *
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository
    ) {
        $this->blockRepository = $blockRepository;
    }

    /**
     * Replace block ids with tokens
     *
     * @param string $content
     * @return string
     */
    public function replaceBlockIds($content)
    {
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesBlockId, PREG_SET_ORDER);
            foreach ($matchesBlockId as $match) {
                $idToReplace = $match[1];
                if ($idToReplace) {
                    //ids may be a list
                    $blockIds = explode(",", $idToReplace);
                    $replacementString = '';
                    foreach ($blockIds as $blockId) {
                        $block = $this->blockRepository->getById($blockId);
                        $identifier = $block->getIdentifier();
                        $replacementString.= $this->tokenStart.$identifier.$this->tokenEnd;
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
