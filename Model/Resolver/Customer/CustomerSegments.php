<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Customer;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\CustomerSegment as CustomerSegmentDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use function is_numeric;

/**
 * Customer Segment field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class CustomerSegments implements ResolverInterface
{
    /**
     * @var CustomerSegmentDataProvider
     */
    private $customerSegmentDataProvider;

    /**
     * @param CustomerSegmentDataProvider $customerSegmentDataProvider
     */
    public function __construct(
        CustomerSegmentDataProvider $customerSegmentDataProvider
    ) {
        $this->customerSegmentDataProvider = $customerSegmentDataProvider;
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
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $segmentIdentifiers = $this->getSegmentIdentifiers($args);
        $segmentData = $this->getSegmentsData($segmentIdentifiers);

        return [
            'items' => $segmentData,
        ];
    }

    /**
     * Get segment identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getSegmentIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Customer Segments should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get segment data
     *
     * @param array $segmentIdentifiers
     * @param int $storeId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getSegmentsData(array $segmentIdentifiers): array
    {
        $segmentsData = [];
        foreach ($segmentIdentifiers as $segmentIdentifier) {
            try {
                if (!is_numeric($segmentIdentifier)) {
                    $segmentsData[$segmentIdentifier] = $this->customerSegmentDataProvider
                        ->getSegmentDataByName($segmentIdentifier);
                } else {
                    $segmentsData[$segmentIdentifier] = $this->customerSegmentDataProvider
                        ->getSegmentDataById((int)$segmentIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $segmentsData[$segmentIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $segmentsData;
    }
}
