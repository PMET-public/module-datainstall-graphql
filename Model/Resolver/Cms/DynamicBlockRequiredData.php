<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Banner\Model\ResourceModel\Banner\CollectionFactory as BannerCollection;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use MagentoEse\DataInstallGraphQl\Model\Converter\RequiredDataInterfaceFactory;

class DynamicBlockRequiredData implements ResolverInterface
{
    /** @var BannerCollection */
    protected $bannerCollection;

    /** @var Converter */
    protected $converter;

    /** @var Authentication */
    protected $authentication;

    /** @var RequiredDataInterfaceFactory */
    protected $requiredDataFactory;
    
    /**
     *
     * @param BannerCollection $bannerCollection
     * @param Converter $converter
     * @param Authentication $authentication
     * @param RequiredDataInterfaceFactory $requiredDataFactory
     * @return void
     */
    public function __construct(
        BannerCollection $bannerCollection,
        Converter $converter,
        Authentication $authentication,
        RequiredDataInterfaceFactory $requiredDataFactory
    ) {
        $this->bannerCollection = $bannerCollection;
        $this->converter = $converter;
        $this->authentication = $authentication;
        $this->requiredDataFactory = $requiredDataFactory;
    }
    
    /**
     * Returns other elements required by the block
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

        if (!empty($value['banner_id'])) {
            $requiredData = $this->requiredDataFactory->create();
            $bannerResults = $this->bannerCollection->create()->
            addFieldToFilter('banner_id', $value['banner_id'])->getItems();
            $banner = current($bannerResults);
            $returnData = $requiredData->
            getRequiredData($banner->getContent());
            return $returnData;
        } else {
            return null;
        }
    }
}
