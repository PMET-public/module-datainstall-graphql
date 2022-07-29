<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\AdminUsers;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\AdminUser as AdminUserProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use function is_numeric;

/**
 * Customer user field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class AdminUsers implements ResolverInterface
{
    /**
     * @var AdminUserProvider
     */
    private $adminUserDataProvider;

    /**
     * @param AdminUserProvider $adminUserDataProvider
     */
    public function __construct(
        AdminUserProvider $adminUserDataProvider
    ) {
        $this->adminUserDataProvider = $adminUserDataProvider;
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
        $userIdentifiers = $this->getUserIdentifiers($args);
        $userData = $this->getUsersData($userIdentifiers);

        return [
            'items' => $userData,
        ];
    }

    /**
     * Get user identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getUserIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Admin Users should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get user data
     *
     * @param array $userIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getUsersData(array $userIdentifiers): array
    {
        $usersData = [];
        foreach ($userIdentifiers as $userIdentifier) {
            try {
                if (!is_numeric($userIdentifier)) {
                    $usersData[$userIdentifier] = $this->adminUserDataProvider
                    ->getAdminUserDataByUserName($userIdentifier);
                } else {
                    $usersData[$userIdentifier] = $this->adminUserDataProvider
                    ->getAdminUserDataById($userIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $usersData[$userIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $usersData;
    }
}
