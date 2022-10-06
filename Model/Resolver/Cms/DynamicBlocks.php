<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\DynamicBlock as DynamicBlockDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use function is_numeric;

/**
 * CMS dynamicBlocks field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class DynamicBlocks implements ResolverInterface
{
    /** @var DynamicBlockDataProvider */
    private $dynamicBlockDataProvider;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param DynamicBlockDataProvider $dynamicBlockDataProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        DynamicBlockDataProvider $dynamicBlockDataProvider,
        Authentication $authentication
    ) {
        $this->dynamicBlockDataProvider = $dynamicBlockDataProvider;
        $this->authentication = $authentication;
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
        $this->authentication->authorize();

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $dynamicBlockIdentifiers = $this->getDynamicBlockIdentifiers($args);
        $dynamicBlocksData = $this->getDynamicBlocksData($dynamicBlockIdentifiers, $storeId);

        return [
            'items' => $dynamicBlocksData,
        ];
    }

    /**
     * Get dynamicBlock identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getDynamicBlockIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of CMS dynamicBlocks should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get dynamicBlocks data
     *
     * @param array $dynamicBlockIdentifiers
     * @param int $storeId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getDynamicBlocksData(array $dynamicBlockIdentifiers, int $storeId): array
    {
        $dynamicBlocksData = [];
        foreach ($dynamicBlockIdentifiers as $dynamicBlockIdentifier) {
            try {
                if (!is_numeric($dynamicBlockIdentifier)) {
                    $dynamicBlocksData[$dynamicBlockIdentifier] = $this->dynamicBlockDataProvider
                        ->getDataByDynamicBlockName($dynamicBlockIdentifier, $storeId);
                } else {
                    $dynamicBlocksData[$dynamicBlockIdentifier] = $this->dynamicBlockDataProvider
                        ->getDataByDynamicBlockId((int)$dynamicBlockIdentifier, $storeId);
                }
            } catch (NoSuchEntityException $e) {
                $dynamicBlocksData[$dynamicBlockIdentifier] =
                new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $dynamicBlocksData;
    }
}
