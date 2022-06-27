<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Magento\CompanyCredit\Api\CreditDataProviderInterface;
use Magento\CompanyCreditGraphQl\Model\Credit\Balance;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Company credit resolver
 */
class Credit implements ResolverInterface
{
    /**
     * @var CreditDataProviderInterface
     */
    private $creditDataProvider;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * @param CreditDataProviderInterface $creditDataProvider
     * @param Balance $balance
     * @param array $allowedResources
     */
    public function __construct(
        CreditDataProviderInterface $creditDataProvider,
        Balance $balance,
        array $allowedResources = []
    ) {
        $this->allowedResources = $allowedResources;
        $this->creditDataProvider = $creditDataProvider;
        $this->balance = $balance;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
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
