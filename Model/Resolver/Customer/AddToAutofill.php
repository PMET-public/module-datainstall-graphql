<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Customer;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AddToAutofill implements ResolverInterface
{
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * Get autofill setting for customer
     *
     * @param Field $field
     * @param Context $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     * @return mixed
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!empty($value["email"])) {
            return $this->getAutofillSetting($value["email"]);
        } else {
            return 'N';
        }
    }

     /**
      * Get autofill setting for customer

      * @param string $email
      * @return string
      */
    private function getAutofillSetting($email)
    {
        $autofill = 'N';
        for ($x = 0; $x <= 17; $x++) {
            if ($this->scopeConfig->getValue(
                'magentoese_autofill/persona_'.$x.'/email_value',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            )==$email) {
                $autofill = 'Y';
                break;
            }
        }
        return $autofill;
    }
}
