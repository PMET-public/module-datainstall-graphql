<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SharedCatalog\Api\CompanyManagementInterface;

class SharedCatalog implements ResolverInterface
{
    /** @var SharedCatalogRepositoryInterface */
    protected $sharedCatalogRepository;

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var CompanyManagementInterface */
    protected $companyManagementInterface;

    /**
     * @param SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CompanyManagementInterface $companyManagementInterface
     */

    public function __construct(
        SharedCatalogRepositoryInterface $sharedCatalogRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CompanyManagementInterface $companyManagementInterface
    ) {
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->companyManagementInterface = $companyManagementInterface;
    }
    
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $companyId = $value['model']->getId();

        $search = $this->searchCriteriaBuilder
        ->addFilter(SharedCatalogInterface::SHARED_CATALOG_ID, 0, 'gt')->create();
        $catalogList = $this->sharedCatalogRepository->getList($search)->getItems();
        foreach ($catalogList as $catalog) {
            $companies = explode(',', str_replace(
                ['"','[',']'],
                '',
                $this->companyManagementInterface->getCompanies($catalog->getId())
            ));
            if (in_array($companyId, $companies, false)) {
                return [
                    'name' => $catalog->getName(),
                    'description' => $catalog->getDescription(),
                    'type' => ($catalog->getType()==0) ? 'Custom' : 'Public'
                ];
            }
        }
        return '';
    }
}
