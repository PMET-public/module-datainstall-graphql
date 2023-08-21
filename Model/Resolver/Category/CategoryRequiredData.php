<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Category;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;
use MagentoEse\DataInstallGraphQl\Model\Converter\RequiredDataInterfaceFactory;

/**
 * Escapes slashes in the category name so it can be used for name lookup by path
 */
class CategoryRequiredData implements ResolverInterface
{
    /** @var Converter */
    protected $converter;

    /** @var RequiredDataInterfaceFactory */
    protected $requiredDataFactory;
    
    /**
     *
     * @param Converter $converter
     * @return void
     */
    public function __construct(
        Converter $converter,
        RequiredDataInterfaceFactory $requiredDataFactory
    ) {
        $this->converter = $converter;
        $this->requiredDataFactory = $requiredDataFactory;
    }
    
    
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $contentToParse = '';
        if (!empty($value['description'])) {
            $contentToParse .= $value['description'].' ';
        }

        if (!empty($value['landing_page'])) {
            $contentToParse .= $this->getBlockIdTags($value['landing_page']);
        }


        $requiredData = $this->requiredDataFactory->create();
        
        return $requiredData->getRequiredData($contentToParse);
    }

    /**
     * Get tags to replace block ids
     *
     * @param string $pageIds
     * @return string
     */
    private function getBlockIdTags($blockId)
    {
        return 'block_id="'.$blockId.'"';
    }
}
