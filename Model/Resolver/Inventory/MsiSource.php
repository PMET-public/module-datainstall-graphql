<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Inventory;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\MsiSource as MsiSourceProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class MsiSource implements ResolverInterface
{
    /** @var MsiSourceProvider */
    private $msiSourceProvider;

    /** @var Authentication */
    private $authentication;

   /**
    *
    * @param MsiSourceProvider $msiSourceProvider
    * @param Authentication $authentication
    * @return void
    */
    public function __construct(
        MsiSourceProvider $msiSourceProvider,
        Authentication $authentication
    ) {
        $this->msiSourceProvider = $msiSourceProvider;
        $this->authentication = $authentication;
    }

    /**
     * Get MSI sources
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

        $sourceIdentifiers = $this->getSourceIdentifiers($args);
        $sourceData = $this->getSourceData($sourceIdentifiers);

        return [
            'items' => $sourceData,
        ];
    }

    /**
     * Get source identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getSourceIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('Source codes of MSI source should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get group data
     *
     * @param array $sourceIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getSourceData(array $sourceIdentifiers): array
    {
        $sourceData = [];
        foreach ($sourceIdentifiers as $sourceIdentifier) {
            try {
                $sourceData[$sourceIdentifier] = $this->msiSourceProvider->getSourcebyCode($sourceIdentifier);
            } catch (NoSuchEntityException $e) {
                $sourceData[$sourceIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $sourceData;
    }
}
