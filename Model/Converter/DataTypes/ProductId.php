<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductId
{
    /** @var string */
    protected $tokenStart = '{{productid sku="';

    /** @var string */
    protected $tokenEnd = '"}}';

    /** @var array */
    protected $regexToSearch = [
        ['regex'=> "/id_path='product\/([0-9]+)'/",
        'substring'=> "id_path='product/"]
    ];
    
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * Replace product ids with tokens
     *
     * @param string $content
     * @return string
     */
    public function replaceProductIds($content)
    {
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesProductId, PREG_SET_ORDER);
            foreach ($matchesProductId as $match) {
                $idToReplace = $match[1];
                if ($idToReplace) {
                    //productids may be a list
                    $productIds = explode(",", $idToReplace);
                    $replacementString = '';
                    foreach ($productIds as $productId) {
                        $replacementString.= $this->getProductIdTag($productId);
                    }
                    $content = str_replace(
                        $search['substring'].$idToReplace,
                        $search['substring'].$replacementString,
                        $content
                    );
                }
            }
        }
        return $content;
    }

    /**
     * Get required product ids
     *
     * @param string $content
     * @param string $type
     * @return array
     */
    public function getRequiredProductIds($content, $type)
    {
        $requiredData = [];
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesProductId, PREG_SET_ORDER);
            foreach ($matchesProductId as $match) {
                $requiredProduct = [];
                $idRequired = $match[1];
                if ($idRequired) {
                    //ids may be a list
                    $productIds = explode(",", $idRequired);
                    foreach ($productIds as $productId) {
                        $product = $this->productRepository->getById($productId);
                        $requiredProduct['name'] = $product->getName();
                        $requiredProduct['id'] = $product->getId();
                        $requiredProduct['type'] = $type;
                        $requiredProduct['identifier'] = $product->getSku();
                        $requiredData[] = $requiredProduct;
                    }
                }
            }
        }
        return $requiredData;
    }

    /**
     * Get tag to replace product id
     *
     * @param int $productId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductIdTag($productId)
    {
        $product = $this->productRepository->getById($productId);
        $sku = $product->getSku();
        return $this->tokenStart.$sku.$this->tokenEnd;
    }
}
