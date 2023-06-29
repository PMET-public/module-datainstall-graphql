<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\Template as TemplateDataProvider;
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

class Templates implements ResolverInterface
{
    /** @var TemplateDataProvider */
    private $templateDataProvider;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param TemplateDataProvider $templateDataProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        TemplateDataProvider $templateDataProvider,
        Authentication $authentication
    ) {
        $this->templateDataProvider = $templateDataProvider;
        $this->authentication = $authentication;
    }

    /**
     * Return Page Builder Templates
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

        $templateIdentifiers = $this->getTemplateIdentifiers($args);
        $templatesData = $this->getTemplatesData($templateIdentifiers, 0);

        return [
            'items' => $templatesData,
        ];
    }

    /**
     * Get template identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getTemplateIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Page Builder templates should be specified'));
        }
        if ($args['identifiers'][0] == '') {
            $args['identifiers'] = $this->templateDataProvider->getAllTemplateIds();
        }

        return $args['identifiers'];
    }

    /**
     * Get templates data
     *
     * @param array $templateIdentifiers
     * @param int $storeId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getTemplatesData(array $templateIdentifiers, int $storeId): array
    {
        $templatesData = [];
        foreach ($templateIdentifiers as $templateIdentifier) {
            try {
                if (!is_numeric($templateIdentifier)) {
                    $templatesData[$templateIdentifier] = $this->templateDataProvider
                        ->getDataByTemplateName($templateIdentifier, $storeId);
                } else {
                    $templatesData[$templateIdentifier] = $this->templateDataProvider
                        ->getDataByTemplateId((int)$templateIdentifier, $storeId);
                }
            } catch (NoSuchEntityException $e) {
                $templatesData[$templateIdentifier] =
                new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $templatesData;
    }
}
