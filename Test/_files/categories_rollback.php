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
$attributeData = ['page_layout'=>null,'landing_page'=>null,'custom_design'=>null];
$category->addData($attributeData);
$categoryRepository->save($category);
