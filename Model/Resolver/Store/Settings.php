<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Store;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

/**
 * @inheritdoc
 */
class Settings implements ResolverInterface
{
    
    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(Authentication $authentication)
    {
        $this->authentication = $authentication;
    }

    /**
     * Get data installer settings
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authentication->authorize();
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
