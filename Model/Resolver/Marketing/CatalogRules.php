<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Marketing;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\CatalogRule as CatalogRuleDataProvider;
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

class CatalogRules implements ResolverInterface
{
    /** @var CatalogRuleDataProvider */
    private $catalogRuleDataProvider;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param CatalogRuleDataProvider $catalogRuleDataProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        CatalogRuleDataProvider $catalogRuleDataProvider,
        Authentication $authentication
    ) {
        $this->catalogRuleDataProvider = $catalogRuleDataProvider;
        $this->authentication = $authentication;
    }

    /**
     * Get Catalog Rules
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
        if ($args['identifiers'][0] == '') {
            $args['identifiers'] = $this->catalogRuleDataProvider->getAllRuleIds();
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
