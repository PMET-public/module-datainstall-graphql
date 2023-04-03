<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

class Company
{
    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param CompanyRepositoryInterface $companyRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get company by name
     *
     * @param string $companyName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByCompanyName(string $companyName): array
    {
        $companyData = $this->fetchCompanyData($companyName, CompanyInterface::NAME);
        return $companyData;
    }

    /**
     * Get company by id
     *
     * @param int $companyId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByCompanyId(int $companyId): array
    {
        $companyData = $this->fetchCompanyData($companyId, CompanyInterface::COMPANY_ID);
        return $companyData;
    }

    /**
     * Fetch company data by field
     *
     * @param mixed $identifier
     * @param string $field
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchCompanyData($identifier, string $field): array
    {
        $companySearch =  $this->searchCriteriaBuilder
        ->addFilter($field, $identifier, 'eq')
        ->create()->setPageSize(1)->setCurrentPage(1);
        $companyList = $this->companyRepository->getList($companySearch);
        /** @var CompanyInterface $company */
        $company = current($companyList->getItems());
        return [
            'model' => $company,
            'isNewCompany' => true
           ];
    }
}
