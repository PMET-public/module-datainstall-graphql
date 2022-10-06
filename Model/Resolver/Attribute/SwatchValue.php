<?php
/**
 * Copyright © Adobe, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Attribute;

use Magento\Framework\Exception\NoSuchEntityException;
use LogicException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Swatches\Helper\Data;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

/**
 * Resolve data for custom attribute metadata requests
 */
class SwatchValue implements ResolverInterface
{

    /** @var Data */
    private $helperData;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param Data $helperData
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(Data $helperData, Authentication $authentication)
    {
        $this->helperData = $helperData;
        $this->authentication = $authentication;
    }

    /**
     * Get Swatch values
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     * @throws LogicException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

        $swatchData = $this->helperData->getSwatchesByOptionsId([$value['value']]);
        if ($swatchData) {
            return $swatchData[$value['value']]['value'];
        } else {
            return null;
        }
    }
}
