<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Customer;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\RewardExchangeRate as ExchangeRateProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class RewardExchangeRates implements ResolverInterface
{
    /** @var ExchangeRateProvider */
    private $exchangeRateProvider;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param ExchangeRateProvider $exchangeRateProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        ExchangeRateProvider $exchangeRateProvider,
        Authentication $authentication
    ) {
        $this->exchangeRateProvider = $exchangeRateProvider;
        $this->authentication = $authentication;
    }

    /**
     * Get rewards exchange rate
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
        $rateIdentifiers = $this->getExchangeRateIdentifiers($args);
        $rateData = $this->getExchangeRateData($rateIdentifiers);

        return [
            'items' => $rateData,
        ];
    }

    /**
     * Get rate identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getExchangeRateIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Exchange Rates should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get rate data
     *
     * @param array $rateIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getExchangeRateData(array $rateIdentifiers): array
    {
        $rateData = [];
        foreach ($rateIdentifiers as $rateIdentifier) {
            try {
                    $rateData[$rateIdentifier] = $this->exchangeRateProvider
                    ->getRateDataById((int)$rateIdentifier);
            } catch (NoSuchEntityException $e) {
                $rateData[$rateIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $rateData;
    }
}
