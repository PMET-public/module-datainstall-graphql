<?php
namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Customer\Api\GroupRepositoryInterface;

class CustomerGroup
{
    protected $tokenStart = '{{customergroup name="';
    protected $tokenEnd = '"}}';
    protected $regexToSearch = [
        ['regex'=> '/"attribute":"group_id","operator":"!=","value":"([0-9]+)"/',
        'substring'=> '"attribute":"group_id","operator":"!=","value":"'],
        ['regex'=> '/"attribute":"group_id","operator":"==","value":"([0-9]+)"/',
        'substring'=> '"attribute":"group_id","operator":"==","value":"'],
        ['regex'=> '/"attribute":"group_id","operator":"","value":"([0-9]+)"/',
        'substring'=> '"attribute":"group_id","operator":"","value":"']
    ];
    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /**
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository
    ) {
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param string $content
     * @return string
     */
    public function replaceCustomerGroupIds($content)
    {
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesGroupId, PREG_SET_ORDER);
            foreach ($matchesGroupId as $match) {
                $idToReplace = $match[1];
                if ($idToReplace) {
                    //ids may be a list
                    $groupIds = explode(",", $idToReplace);
                    $replacementString = '';
                    foreach ($groupIds as $groupId) {
                        $group = $this->groupRepository->getById($groupId);
                        $groupId = $group->getCode();
                        $replacementString.= $this->tokenStart.$groupId.$this->tokenEnd;
                    }
                    $content = str_replace($search['substring'].$idToReplace, $replacementString, $content);
                }
            }
        }
        return $content;
    }
}
