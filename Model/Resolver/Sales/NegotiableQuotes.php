<?php

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Sales;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class NegotiableQuotes implements ResolverInterface
{

    private Authentication $authentication;
    private \MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\NegotiableQuotes $negotiableQuotes;

    public function __construct(
        Authentication $authentication,
        \MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\NegotiableQuotes $negotiableQuotes
    )
    {
        $this->authentication = $authentication;
        $this->negotiableQuotes = $negotiableQuotes;
    }

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $this->authentication->authorize();
        $currentPage = isset($args['currentPage']) ? (int)$args['currentPage'] : 1;
        $pageSize = isset($args['pageSize']) ? (int)$args['pageSize'] : 20;

        if ($currentPage < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($pageSize < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        $filterArgs = $args['filter'] ?? [];

        $sortArgs = $args['sort'] ?? [];

        $quoteIdentifiers = $this->getQuoteIdentifiers($args);
        return $this->negotiableQuotes->getNegotiableQuotes(
            $quoteIdentifiers,
            $filterArgs,
            $currentPage,
            $pageSize,
            $sortArgs
        );
    }

    /**
     * Get company identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getQuoteIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('ID of quotes should be specified'));
        }

        return $args['identifiers'];
    }
}
