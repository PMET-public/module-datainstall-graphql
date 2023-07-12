<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Magento\CompanyGraphQl\Model\Company\Users as CompanyUsers;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;

class Users implements ResolverInterface
{
    /** @var CompanyUsers */
    private $companyUsers;

    /** @var ExtractCustomerData */
    private $customerData;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param CompanyUsers $companyUsers
     * @param ExtractCustomerData $customerData
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        CompanyUsers $companyUsers,
        ExtractCustomerData $customerData,
        Authentication $authentication
    ) {
        $this->companyUsers = $companyUsers;
        $this->customerData = $customerData;
        $this->authentication = $authentication;
    }

   /**
    * Get company users
    *
    * @param Field $field
    * @param ContextInterface $context
    * @param ResolveInfo $info
    * @param array|null $value
    * @param array|null $args
    * @return mixed|Value
    * @throws GraphQlInputException
    * @throws LocalizedException
    */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        $company = $value['model'];
        if ($company) {
            $searchResults = $this->companyUsers->getCompanyUsers($company, $args);
            $companyUsers = [];

            foreach ($searchResults->getItems() as $companyUser) {
                $companyUsers[] = $this->customerData->execute($companyUser);
            }

            $pageSize = $searchResults->getSearchCriteria()->getPageSize();

            return [
                'items' => $companyUsers,
                'total_count' => $searchResults->getTotalCount(),
                'page_info' => [
                    'page_size' => $pageSize,
                    'current_page' => $searchResults->getSearchCriteria()->getCurrentPage(),
                    'total_pages' => $pageSize ? ((int)ceil($searchResults->getTotalCount() / $pageSize)) : 0
                ]
            ];
        } else {
            return [
                'items' => [],
                'total_count' => 0,
                'page_info' => [
                    'page_size' => 0,
                    'current_page' => 0,
                    'total_pages' => 0
                ]
            ];
        }
    }
}
