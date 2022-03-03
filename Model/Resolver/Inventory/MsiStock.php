<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Inventory;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\MsiStock as MsiStockProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Msi Stock field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class MsiStock implements ResolverInterface
{
    /**
     * @var MsiStockProvider
     */
    private $msiStockProvider;

    /**
     * @param MsiStockProvider $msiStockProvider
     */
    public function __construct(
        MsiStockProvider $msiStockProvider
    ) {
        $this->msiStockProvider = $msiStockProvider;
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
        $stockIdentifiers = $this->getStockIdentifiers($args);
        $stockData = $this->getStockData($stockIdentifiers);

        return [
            'items' => $stockData,
        ];
    }

    /**
     * Get stock identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getStockIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('Stock codes of MSI stock should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get stock data
     *
     * @param array $stockIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getStockData(array $stockIdentifiers): array
    {
        $stockData = [];
        foreach ($stockIdentifiers as $stockIdentifier) {
            try {
                if (!is_numeric($stockIdentifier)) {
                    $stockData[$stockIdentifier] = $this->msiStockProvider
                        ->getStockDataByName($stockIdentifier);
                } else {
                    $stockData[$stockIdentifier] = $this->msiStockProvider
                        ->getStockDataById((int)$stockIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $stockData[$stockIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $stockData;
    }
}
