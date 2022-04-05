<?php
/**
 * Copyright © Adobe, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Attribute;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Swatches\Helper\Data;

/**
 * Resolve data for custom attribute metadata requests
 */
class SwatchValue implements ResolverInterface
{

     /**
      * @var Data
      */
    private $helperData;

     /**
      * @param Data $helperData
      */

    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
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
        $swatchData = $this->helperData->getSwatchesByOptionsId([$value['value']]);
        if ($swatchData) {
            return $swatchData[$value['value']]['value'];
        } else {
            return null;
        }
    }
}
