<?php
namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Eav\Api\AttributeOptionManagementInterface;

class ProductAttribute
{
    protected $tokenStart = '{{productattribute code="';
    protected $tokenEnd = '"}}';
    protected $regexToSearch = [
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"==","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"==","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"!=","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"!=","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"\!\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"!()","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"()","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"{}","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"!{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"!{}","value":["',
        'delimiter'=>'","'],

        // //this may be redundant
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"==","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"==","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"!=","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"!=","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"\!\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"!()","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"()","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"{}","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"!{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"!{}","value":["',
        'delimiter'=>'","'],

        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"==","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"==","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"!=","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"!=","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"\!\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"!()","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"()","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"{}","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"!{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"!{}","value":["',
        'delimiter'=>'","'],
        //Block
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`==`,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`==`,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`!=`,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`!=`,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:``,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:``,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`\!\(\)`,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`!()`,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`\(\)`,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`()`,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`{}`,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`{}`,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`!{}`,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`!{}`,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`\^\[\^\]`,`value`:\[([0-9,`]+)\]/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`^[^]`,`value`:[',
        'delimiter'=>'`,`'],
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
                $attributeOptions = $this->attributeOptionManagement->getItems(4, $attributeCode);
                if ($idToReplace) {
                    //may be list of ids
                    $optionIds = explode(",", str_replace('"', '', str_replace('`', '', $idToReplace)));
                    $replacementArr = [];
                    foreach ($optionIds as $optionId) {
                        foreach ($attributeOptions as $attributeOption) {
                            if ($attributeOption->getvalue()==$optionId) {
                                $replacementArr[]= $this->tokenStart.$attributeCode.":".$attributeOption->getLabel().$this->tokenEnd;
                                break;
                            }
                        }
                    }
                    $replacementString = implode($search['delimiter'], $replacementArr);
                    $content = $this->strLreplace($search['substringstart'].$attributeCode.$search['substringend'].
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
