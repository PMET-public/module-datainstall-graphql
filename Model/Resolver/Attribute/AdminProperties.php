<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Attribute;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\EavGraphQl\Model\Resolver\Query\Type;
use Magento\EavGraphQl\Model\Resolver\Query\Attribute;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Eav\Api\Data\AttributeInterface;

/**
 * Resolve data for custom attribute metadata requests
 */
class AdminProperties implements ResolverInterface
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @param Type $type
     * @param Attribute $attribute
     */
    public function __construct(Type $type, Attribute $attribute)
    {
        $this->type = $type;
        $this->attribute = $attribute;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        

        return ['admin_properties' => $this->getStorefrontProperties()];
    }

    /**
     * Format storefront properties
     *
     * @param AttributeInterface $attribute
     * @return array
     */
    private function getStorefrontProperties()
    {
        return [
            'foo'=> 'foo',
            'bar'=> 'bar',
            'shutupmeg'=> 'shutupmeg'
        ];
    }

    /**
     * Return enum for resolving use in layered navigation
     *
     * @return string[]
     */
    private function getLayeredNavigationPropertiesEnum() {
        return [
            0 => 'NO',
            1 => 'FILTERABLE_WITH_RESULTS',
            2 => 'FILTERABLE_NO_RESULT'
        ];
    }

    /**
     * Create GraphQL input exception for an invalid attribute input
     *
     * @param array $attribute
     * @return GraphQlInputException
     */
    private function createInputException(array $attribute) : GraphQlInputException
    {
        $isCodeSet = isset($attribute['attribute_code']);
        $isEntitySet = isset($attribute['entity_type']);
        $messagePart = !$isCodeSet ? 'attribute_code' : 'entity_type';
        $messagePart .= !$isCodeSet && !$isEntitySet ? '/entity_type' : '';
        $identifier = "Empty AttributeInput";
        if ($isCodeSet) {
            $identifier = 'attribute_code: ' . $attribute['attribute_code'];
        } elseif ($isEntitySet) {
            $identifier = 'entity_type: ' . $attribute['entity_type'];
        }

        return new GraphQlInputException(
            __(
                'Missing %1 for the input %2.',
                [$messagePart, $identifier]
            )
        );
    }
}
