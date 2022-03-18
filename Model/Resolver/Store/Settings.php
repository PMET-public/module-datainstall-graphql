<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Store;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 */
class Settings implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $r = $context->getExtensionAttributes();
        $returnArray = [
            'site_code' => $context->getExtensionAttributes()->getStore()->getWebsite()->getCode(),
            'store_code' => $context->getExtensionAttributes()->getStore()->getGroup()->getCode(),
            'store_view_code' => $context->getExtensionAttributes()->getStore()->getCode(),
        ];
        
        if (!empty($args['restrictProductsFromViews'])) {
            $returnArray['restrict_products_from_views'] =  $args['restrictProductsFromViews'];
        }

        if (!empty($args['productImageImportDirectory'])) {
            $returnArray['product_image_import_directory'] =  $args['productImageImportDirectory'];
        }

        if (!empty($args['productValidationStrategy'])) {
            $returnArray['product_validation_strategy'] =  $args['productValidationStrategy'];
        }
        
        return $returnArray;
    }
}
