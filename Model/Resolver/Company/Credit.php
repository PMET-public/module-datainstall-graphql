<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Magento\CompanyCredit\Api\CreditDataProviderInterface;
use Magento\CompanyCreditGraphQl\Model\Credit\Balance;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class Credit implements ResolverInterface
{
    /** @var array */
    private $allowedResources;

    /** @var CreditDataProviderInterface */
    private $creditDataProvider;

    /** @var Balance */
    private $balance;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param CreditDataProviderInterface $creditDataProvider
     * @param Balance $balance
     * @param Authentication $authentication
     * @param array $allowedResources
     * @return void
     */
    public function __construct(
        CreditDataProviderInterface $creditDataProvider,
        Balance $balance,
        Authentication $authentication,
        array $allowedResources = []
    ) {
        $this->allowedResources = $allowedResources;
        $this->creditDataProvider = $creditDataProvider;
        $this->balance = $balance;
        $this->authentication = $authentication;
    }

    /**
     * Get company credit details
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     * @throws LocalizedException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authentication->authorize();

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $company = $value['model'];
        if ($company) {
            $credit = $this->creditDataProvider->get($company->getId());
            $currencyCode = $credit->getCurrencyCode();
            return [
                'outstanding_balance' => $this->balance->formatData($currencyCode, (float)$credit->getBalance()),
                'available_credit' => $this->balance->formatData($currencyCode, (float)$credit->getAvailableLimit()),
                'credit_limit' => $this->balance->formatData($currencyCode, (float)$credit->getCreditLimit())
            ];
        } else {
            return [
                'outstanding_balance' => '',
                'available_credit' => '',
                'credit_limit' => ''
            ];
        }
    }
}
