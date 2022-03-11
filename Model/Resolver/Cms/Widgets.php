<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\Widget as WidgetDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use function is_numeric;

/**
 * CMS widgets field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class Widgets implements ResolverInterface
{
    /**
     * @var WidgetDataProvider
     */
    private $widgetDataProvider;

    /**
     * @param WidgetDataProvider $widgetDataProvider
     */
    public function __construct(
        WidgetDataProvider $widgetDataProvider
    ) {
        $this->widgetDataProvider = $widgetDataProvider;
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
        $widgetIdentifiers = $this->getWidgetIdentifiers($args);
        $widgetsData = $this->getWidgetsData($widgetIdentifiers);

        return [
            'items' => $widgetsData,
        ];
    }

    /**
     * Get widget identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getWidgetIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Widgets should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get widgets data
     *
     * @param array $widgetIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getWidgetsData(array $widgetIdentifiers): array
    {
        $widgetsData = [];
        foreach ($widgetIdentifiers as $widgetIdentifier) {
            try {
                if (!is_numeric($widgetIdentifier)) {
                    $widgetsData[$widgetIdentifier] = $this->widgetDataProvider
                        ->getWidgetDataByName($widgetIdentifier);
                } else {
                    $widgetsData[$widgetIdentifier] = $this->widgetDataProvider
                        ->getWidgetDataById((int)$widgetIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $widgetsData[$widgetIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $widgetsData;
    }
}
