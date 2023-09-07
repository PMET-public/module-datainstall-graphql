<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Cms\Api\PageRepositoryInterface;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class PageId implements ResolverInterface
{
    /** @var PageRepositoryInterface */
    protected $pageRepository;

    /** @var Converter */
    protected $converter;

    /** @var Authentication */
    protected $authentication;
    
    /**
     *
     * @param PageRepositoryInterface $pageRepository
     * @param Converter $converter
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        Converter $converter,
        Authentication $authentication
    ) {
        $this->pageRepository = $pageRepository;
        $this->converter = $converter;
        $this->authentication = $authentication;
    }
    
    /**
     * Returns raw content of the page
     *
     * @param Field $field
     * @param Context $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     * @return mixed
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authentication->authorize();

        if (!empty($value['page_id'])) {
            return $value['page_id'];
        } else {
            return null;
        }
    }
}
