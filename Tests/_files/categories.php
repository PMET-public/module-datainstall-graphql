<?php

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/**
 * @var $category CategoryInterface
 * @var $categoryRepository CategoryRepositoryInterface
 */
$categoryRepository = $objectManager->create(CategoryRepositoryInterface::class);
$category = $categoryRepository->get(6);
$attributeData = ['page_layout'=>'2columns-left','landing_page'=>12,'custom_design'=>2];
$category->addData($attributeData);
$categoryRepository->save($category);
