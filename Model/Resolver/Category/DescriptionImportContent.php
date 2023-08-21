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

/**
 * Escapes slashes in the category name so it can be used for name lookup by path
 */
class DescriptionImportContent implements ResolverInterface
{
    /** @var Converter */
    protected $converter;
    
    /**
     *
     * @param Converter $converter
     * @return void
     */
    public function __construct(
        Converter $converter
    ) {
        $this->converter = $converter;
    }
        
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!empty($value['description'])) {
            return $this->converter->convertContent($value['description']);
        } else {
            return null;
        }
    }
}
