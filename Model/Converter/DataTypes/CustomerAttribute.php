<?php
namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Eav\Api\AttributeOptionManagementInterface;

class CustomerAttribute
{
    protected $tokenStart = '{{customerattribute code="';
    protected $tokenEnd = '"}}';
    protected $regexToSearch = [
        ['regex'=> '/Customer\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"==","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Customer\\Attributes","attribute":"',
        'substringend'=>'","operator":"==","value":["'],
        ['regex'=> '/Customer\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"!=","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Customer\\Attributes","attribute":"',
        'substringend'=>'","operator":"!=","value":["'],
        ['regex'=> '/Customer\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Customer\\Attributes","attribute":"',
        'substringend'=>'","operator":"","value":["'],
        ['regex'=> '/Customer\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"\!\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Customer\\Attributes","attribute":"',
        'substringend'=>'","operator":"!()","value":["'],
        ['regex'=> '/Customer\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Customer\\Attributes","attribute":"',
        'substringend'=>'","operator":"()","value":["'],
        ['regex'=> '/Customer\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Customer\\Attributes","attribute":"',
        'substringend'=>'","operator":"{}","value":["'],
        ['regex'=> '/Customer\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"!{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Customer\\Attributes","attribute":"',
        'substringend'=>'","operator":"!{}","value":["'],

        ['regex'=> '/Customer\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"==","value":"([0-9]+)"/',
        'substringstart'=> 'Customer\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"==","value":"']
    ];
    /** @var AttributeOptionManagementInterface */
    protected $attributeOptionManagement;

    /**
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     */
    public function __construct(
        AttributeOptionManagementInterface $attributeOptionManagement
    ) {
        $this->attributeOptionManagement = $attributeOptionManagement;
    }

    /**
     * @param string $content
     * @return string
     */
    public function replaceAttributeOptionIds($content)
    {
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesOptions, PREG_SET_ORDER);
            foreach ($matchesOptions as $match) {
                $attributeCode = $match[1];
                $idToReplace = $match[2];
                $attributeOptions = $this->attributeOptionManagement->getItems(1, $attributeCode);
                if ($idToReplace) {
                    //may be list of ids
                    $optionIds = explode(",", str_replace('"', '', $idToReplace));
                    $replacementString = '';
                    foreach ($optionIds as $optionId) {
                        foreach ($attributeOptions as $attributeOption) {
                            if ($attributeOption->getvalue()==$optionId) {
                                $replacementString.= $this->tokenStart.$attributeCode.":".
                                $attributeOption->getLabel().$this->tokenEnd.'","';
                                break;
                            }
                        }
                    }
                    $replacementString = $this->strLreplace('","', "", $replacementString);
                    $toFind = $search['substringstart'].$attributeCode.$search['substringend'].$idToReplace;
                    $content = str_replace($search['substringstart'].$attributeCode.$search['substringend'].
                    $idToReplace, $search['substringstart'].$attributeCode.$search['substringend'].
                    $replacementString, $content);
                }
            }
        }
        return $content;
    }
     /**
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
