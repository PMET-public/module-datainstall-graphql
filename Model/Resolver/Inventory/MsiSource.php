<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Inventory;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\MsiSource as MsiSourceProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Msi Source field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class MsiSource implements ResolverInterface
{
    /**
     * @var MsiSourceProvider
     */
    private $msiSourceProvider;

    /**
     * @param MsiSourceProvider $msiSourceProvider
     */
    public function __construct(
        MsiSourceProvider $msiSourceProvider
    ) {
        $this->msiSourceProvider = $msiSourceProvider;
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
        $sourceIdentifiers = $this->getSourceIdentifiers($args);
        $sourceData = $this->getSourceData($sourceIdentifiers);

        return [
            'items' => $sourceData,
        ];
    }

    /**
     * Get source identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getSourceIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('Source codes of MSI source should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get group data
     *
     * @param array $sourceIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getSourceData(array $sourceIdentifiers): array
    {
        $sourceData = [];
        foreach ($sourceIdentifiers as $sourceIdentifier) {
            try {
                $sourceData[$sourceIdentifier] = $this->msiSourceProvider->getSourcebyCode($sourceIdentifier);
            } catch (NoSuchEntityException $e) {
                $sourceData[$sourceIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $sourceData;
    }
}
