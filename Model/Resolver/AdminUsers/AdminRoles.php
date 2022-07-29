<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\AdminUsers;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\AdminRole as AdminRoleProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use function is_numeric;

/**
 * Customer role field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class AdminRoles implements ResolverInterface
{
    /**
     * @var AdminRoleProvider
     */
    private $adminRoleDataProvider;

    /**
     * AdminRoles Resolver constructor
     *
     * @param AdminRoleProvider $adminRoleDataProvider
     */
    public function __construct(
        AdminRoleProvider $adminRoleDataProvider
    ) {
        $this->adminRoleDataProvider = $adminRoleDataProvider;
    }

    /**
     * Admin Roles Resolver
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
        $roleIdentifiers = $this->getRoleIdentifiers($args);
        $roleData = $this->getRolesData($roleIdentifiers);

        return [
            'items' => $roleData,
        ];
    }

    /**
     * Get role identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getRoleIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Admin Roles should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get role data
     *
     * @param array $roleIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getRolesData(array $roleIdentifiers): array
    {
        $rolesData = [];
        $rolesDataReturn = [];
        foreach ($roleIdentifiers as $roleIdentifier) {
            try {
                if (!is_numeric($roleIdentifier)) {
                    $rolesData[$roleIdentifier] = $this->adminRoleDataProvider
                    ->getRoleDataByName($roleIdentifier);
                } else {
                    $rolesData[$roleIdentifier] = $this->adminRoleDataProvider
                    ->getRoleDataById($roleIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $rolesData[$roleIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
            $rolesDataReturn = array_merge($rolesDataReturn, $rolesData[$roleIdentifier]);
        }
        return $rolesDataReturn;
    }
}
