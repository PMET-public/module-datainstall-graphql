<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Marketing;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\Upsell as UpsellDataProvider;
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

class Upsells implements ResolverInterface
{
    /** @var UpsellDataProvider */
    private $upsellDataProvider;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param UpsellDataProvider $upsellDataProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        UpsellDataProvider $upsellDataProvider,
        Authentication $authentication
    ) {
        $this->upsellDataProvider = $upsellDataProvider;
        $this->authentication = $authentication;
    }

    /**
     * Get Upsells
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

        $upsellRuleIdentifiers = $this->getUpsellIdentifiers($args);
        $upsellRuleData = $this->getUpsellsData($upsellRuleIdentifiers);

        return [
            'items' => $upsellRuleData,
        ];
    }

    /**
     * Get upsell rule identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getUpsellIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Related Product Rules should be specified'));
        }
        if ($args['identifiers'][0] == '') {
            $args['identifiers'] = $this->upsellDataProvider->getAllRuleIds();
        }
        return $args['identifiers'];
    }

    /**
     * Get upsell rule data
     *
     * @param array $upsellRuleIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getUpsellsData(array $upsellRuleIdentifiers): array
    {
        $upsellRulesData = [];
        foreach ($upsellRuleIdentifiers as $upsellRuleIdentifier) {
            try {
                if (!is_numeric($upsellRuleIdentifier)) {
                    $upsellRulesData[$upsellRuleIdentifier] = $this->upsellDataProvider
                        ->getUpsellRuleDataByName($upsellRuleIdentifier);
                } else {
                    $upsellRulesData[$upsellRuleIdentifier] = $this->upsellDataProvider
                        ->getUpsellRuleDataById((int)$upsellRuleIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $upsellRulesData[$upsellRuleIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $upsellRulesData;
    }
}
