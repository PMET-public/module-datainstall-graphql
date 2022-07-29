<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Marketing;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\CatalogRule as CatalogRuleDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use function is_numeric;

/**
 * Customer Segment field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class CatalogRules implements ResolverInterface
{
    /**
     * @var CatalogRuleDataProvider
     */
    private $catalogRuleDataProvider;

    /**
     * @param CatalogRuleDataProvider $catalogRuleDataProvider
     */
    public function __construct(
        CatalogRuleDataProvider $catalogRuleDataProvider
    ) {
        $this->catalogRuleDataProvider = $catalogRuleDataProvider;
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
        $catalogRuleIdentifiers = $this->getCatalogRuleIdentifiers($args);
        $catalogRuleData = $this->getCatalogRulesData($catalogRuleIdentifiers);

        return [
            'items' => $catalogRuleData,
        ];
    }

    /**
     * Get catalog rule identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getCatalogRuleIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Catalog Rules should be specified'));
        }
        return $args['identifiers'];
    }

    /**
     * Get catalog rule data
     *
     * @param array $catalogRuleIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getCatalogRulesData(array $catalogRuleIdentifiers): array
    {
        $catalogRulesData = [];
        foreach ($catalogRuleIdentifiers as $catalogRuleIdentifier) {
            try {
                if (!is_numeric($catalogRuleIdentifier)) {
                    $catalogRulesData[$catalogRuleIdentifier] = $this->catalogRuleDataProvider
                        ->getCatalogRuleDataByName($catalogRuleIdentifier);
                } else {
                    $catalogRulesData[$catalogRuleIdentifier] = $this->catalogRuleDataProvider
                        ->getCatalogRuleDataById((int)$catalogRuleIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $catalogRulesData[$catalogRuleIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $catalogRulesData;
    }
}
