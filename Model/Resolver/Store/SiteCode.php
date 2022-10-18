<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Store;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\WebsiteRepositoryInterface;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class SiteCode implements ResolverInterface
{
    
    /** @var WebsiteRepositoryInterface */
    protected $websiteRepositoryInterface;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param WebsiteRepositoryInterface $websiteRepositoryInterface
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepositoryInterface,
        Authentication $authentication
    ) {
        $this->websiteRepositoryInterface = $websiteRepositoryInterface;
        $this->authentication = $authentication;
    }
    
    /**
     * Get website code
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authentication->authorize();

        $websiteId = $context->getExtensionAttributes()->getStore()->getWebsiteId();
        return $this->websiteRepositoryInterface->get($websiteId)->getCode();
    }
}
