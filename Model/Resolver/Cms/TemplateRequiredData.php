<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\PageBuilder\Model\ResourceModel\Template\CollectionFactory as TemplateCollection;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use MagentoEse\DataInstallGraphQl\Model\Converter\RequiredDataInterfaceFactory;

class TemplateRequiredData implements ResolverInterface
{
    /** @var TemplateCollection */
    protected $templateCollection;

    /** @var Converter */
    protected $converter;

    /** @var Authentication */
    protected $authentication;

    /** @var RequiredDataInterfaceFactory */
    protected $requiredDataFactory;
    
    /**
     *
     * @param TemplateCollection $templateCollection
     * @param Converter $converter
     * @param Authentication $authentication
     * @param RequiredDataInterfaceFactory $requiredDataFactory
     * @return void
     */
    public function __construct(
        TemplateCollection $templateCollection,
        Converter $converter,
        Authentication $authentication,
        RequiredDataInterfaceFactory $requiredDataFactory
    ) {
        $this->templateCollection = $templateCollection;
        $this->converter = $converter;
        $this->authentication = $authentication;
        $this->requiredDataFactory = $requiredDataFactory;
    }
    
    /**
     * Returns other elements required by the template
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
        if (!empty($value['template_id'])) {
            $requiredData = $this->requiredDataFactory->create();
            $template = $this->templateCollection->create()
            ->addFieldToFilter('template_id', ['eq' => $value['template_id']])->getFirstItem();
            $returnData = $requiredData->
            getRequiredData($template->getTemplate());
            return $returnData;
        } else {
            return null;
        }
    }
}
