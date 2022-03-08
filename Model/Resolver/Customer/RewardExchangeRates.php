<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Customer;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\RewardExchangeRate as ExchangeRateProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use function is_numeric;

/**
 * Exchange Rate resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class RewardExchangeRates implements ResolverInterface
{
    /**
     * @var ExchangeRateProvider
     */
    private $exchangeRateProvider;

    /**
     * @param ExchangeRateProvider $exchangeRateProvider
     */
    public function __construct(
        ExchangeRateProvider $exchangeRateProvider
    ) {
        $this->exchangeRateProvider = $exchangeRateProvider;
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
