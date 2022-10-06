<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\Company as CompanyDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use function is_numeric;

class Company implements ResolverInterface
{
     /** @var CompanyDataProvider */
    private $companyDataProvider;

    /** @var Authentication */
    private $authentication;

    /**
     *
     * @param CompanyDataProvider $companyDataProvider
     * @param Authentication $authentication
     * @return void
     */
    public function __construct(
        CompanyDataProvider $companyDataProvider,
        Authentication $authentication
    ) {
        $this->companyDataProvider = $companyDataProvider;
        $this->authentication = $authentication;
    }

   /**
    * Get Company Data
    *
    * @param Field $field
    * @param ContextInterface $context
    * @param ResolveInfo $info
    * @param array|null $value
    * @param array|null $args
    * @return mixed|Value
    * @throws GraphQlInputException
    * @throws GraphQlNoSuchEntityException
    */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

        $companyIdentifiers = $this->getCompanyIdentifiers($args);
        $companyData = $this->getCompanyData($companyIdentifiers);

        return [
            'items' => $companyData,
        ];
    }

    /**
     * Get company identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getCompanyIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('Name or ID of companies should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get company data
     *
     * @param array $companyIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getCompanyData(array $companyIdentifiers): array
    {
        $companyData = [];
        foreach ($companyIdentifiers as $companyIdentifier) {
            try {
                if (!is_numeric($companyIdentifier)) {
                    $companyData[$companyIdentifier] = $this->companyDataProvider
                        ->getDataByCompanyName($companyIdentifier);
                } else {
                    $companyData[$companyIdentifier] = $this->companyDataProvider
                        ->getDataByCompanyId((int)$companyIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $companyData[$companyIdentifier] =
                new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $companyData;
    }
}
