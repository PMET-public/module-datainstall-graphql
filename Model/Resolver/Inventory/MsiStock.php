<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Inventory;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\MsiStock as MsiStockProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class MsiStock implements ResolverInterface
{
    /** @var MsiStockProvider */
    private $msiStockProvider;

    /** @var Authentication */
    private $authentication;

   /**
    *
    * @param MsiStockProvider $msiStockProvider
    * @param Authentication $authentication
    * @return void
    */
    public function __construct(
        MsiStockProvider $msiStockProvider,
        Authentication $authentication
    ) {
        $this->msiStockProvider = $msiStockProvider;
        $this->authentication = $authentication;
    }

    /**
     * Get MSI Stock Settings
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
