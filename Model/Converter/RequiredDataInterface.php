<?php

namespace MagentoEse\DataInstallGraphQl\Model\Converter;

use Magento\Framework\Api\ExtensibleDataInterface;

interface RequiredDataInterface
{
    /***
     * Types of Data to that can be required
     */
    public const PRODUCTS = 'product';
    public const BLOCKS = 'block';
    public const CATEGORIES = 'category';
    public const CUSTOMER_GROUPS = 'customer_group';
    public const CUSTOMER_ATTRIBUTES = 'customer_attribute';
    public const CUSTOMER_SEGMENTS = 'customer_segment';
    public const DYNAMIC_BLOCKS = 'dynamic_block';
    public const PAGES = 'page';
    public const PRODUCT_ATTRIBUTES = 'product_attribute';
    public const PRODUCT_ATTRIBUTE_SETS = 'product_attribute_set';
    /***/

    /**
     * Get required data
     *
     * @param string $content
     * @return array
     */
    public function getRequiredData($content) : array;
}
