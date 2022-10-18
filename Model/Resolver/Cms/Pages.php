<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\CmsGraphQl\Model\Resolver\DataProvider\Page as PageDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use function is_numeric;

class Pages implements ResolverInterface
{
    /**  @var PageDataProvider */
    private $pageDataProvider;

    /**
     *
     * @param PageDataProvider $pageDataProvider
     * @return void
     */
    public function __construct(
        PageDataProvider $pageDataProvider
    ) {
        $this->pageDataProvider = $pageDataProvider;
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
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $pageIdentifiers = $this->getPageIdentifiers($args);
        $pagesData = $this->getPagesData($pageIdentifiers, $storeId);

        return [
            'items' => $pagesData,
        ];
    }

    /**
     * Get page identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getPageIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of CMS pages should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get pages data
     *
     * @param array $pageIdentifiers
     * @param int $storeId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getPagesData(array $pageIdentifiers, int $storeId): array
    {
        $pagesData = [];
        foreach ($pageIdentifiers as $pageIdentifier) {
            try {
                if (!is_numeric($pageIdentifier)) {
                    $pagesData[$pageIdentifier] = $this->pageDataProvider
                        ->getDataByPageIdentifier($pageIdentifier, $storeId);
                } else {
                    $pagesData[$pageIdentifier] = $this->pageDataProvider
                        ->getDataByPageId((int)$pageIdentifier, $storeId);
                }
            } catch (NoSuchEntityException $e) {
                $pagesData[$pageIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $pagesData;
    }
}
