<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Configuration
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }
   
    /**
     * Get settings Data
     *
     * @param string $path
     * @param string $storeCode
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSettingsData(string $path, string $storeCode): array
    {
        //check to see if value is set. If it's not, ignore it
        // left in for reference. Returning the "resolved" value is more useful, but may revisit in the future
        // if (!$this->scopeConfig->isSetFlag($path, 'stores', $storeCode)) {
        //     return [];
        // }
        $value = $this->scopeConfig->getValue($path, 'stores', $storeCode);
        if ($value==null) {
            return [];
        } else {
            return [
                'path'=>$path,
                'scope'=>'stores',
                'scope_code'=>$storeCode,
                'value'=>$value
            ];
        }
    }
}
