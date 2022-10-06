<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Marketing;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\CartRule as CartRuleDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use function is_numeric;

/**
 * Customer Segment field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class CartRules implements ResolverInterface
{
    /** @var CartRuleDataProvider */
    private $cartRuleDataProvider;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param CartRuleDataProvider $cartRuleDataProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        CartRuleDataProvider $cartRuleDataProvider,
        Authentication $authentication
    ) {
        $this->cartRuleDataProvider = $cartRuleDataProvider;
        $this->authentication = $authentication;
    }

    /**
     * Get Cart rules
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

        $cartRuleIdentifiers = $this->getCartRuleIdentifiers($args);
        $cartRuleData = $this->getCartRulesData($cartRuleIdentifiers);

        return [
            'items' => $cartRuleData,
        ];
    }

    /**
     * Get cart rule identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getCartRuleIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Cart Rules should be specified'));
        }
        return $args['identifiers'];
    }

    /**
     * Get cart rule data
     *
     * @param array $cartRuleIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getCartRulesData(array $cartRuleIdentifiers): array
    {
        $cartRulesData = [];
        foreach ($cartRuleIdentifiers as $cartRuleIdentifier) {
            try {
                if (!is_numeric($cartRuleIdentifier)) {
                    $cartRulesData[$cartRuleIdentifier] = $this->cartRuleDataProvider
                        ->getCartRuleDataByName($cartRuleIdentifier);
                } else {
                    $cartRulesData[$cartRuleIdentifier] = $this->cartRuleDataProvider
                        ->getCartRuleDataById((int)$cartRuleIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $cartRulesData[$cartRuleIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $cartRulesData;
    }
}
