<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Export;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use Magento\ImportExport\Model\Export\Entity\ExportInfoFactory;
use Magento\ImportExport\Api\ExportManagementInterface;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;

/**
 * Msi Source field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class ProductExport implements ResolverInterface
{
    /** @var ExportInfoFactory */
    private $exportInfoFactory;

    /** @var ExportManagementInterface */
    private $exportManager;

     /** @var LocaleResolver */
     private $localeResolver;

    /** @var Authentication */
    private $authentication;

   /**
    *
    * @param ExportInfoFactory $exportInfoFactory
    * @param ExportManagementInterface $exportManager
    * @param LocaleResolver $localeResolver
    * @param Authentication $authentication
    * @return void
    */
    public function __construct(
        ExportInfoFactory $exportInfoFactory,
        ExportManagementInterface $exportManager,
        LocaleResolver $localeResolver,
        Authentication $authentication
    ) {
        $this->exportInfoFactory = $exportInfoFactory;
        $this->exportManager = $exportManager;
        $this->localeResolver = $localeResolver;
        $this->authentication = $authentication;
    }

    /**
     * Get Product Export Data
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

        if (empty($args['categoryIds'])) {
            $filter = [];
        } else {
            $filter = ['category_ids'=>$args['categoryIds'][0]];
        }

         /** @var ExportInfoFactory $dataObject */
        $exportInfo = $this->exportInfoFactory->create(
            'csv', //file format
            'catalog_product',
            $filter, //filter
            [], //skip attributes is done by attribute id, not by attribute code
            $this->localeResolver->getLocale()
        );

        $data = $this->exportManager->export($exportInfo);
        //get # of colums in header row
        $headerColumns = explode(",", explode("\n", $data)[0]);

        //$array = array_map("str_getcsv", explode("\n", count($headerColumns)));
        $array = $this->csvToArray($data, count($headerColumns));
        $exploded = explode("\n", $data);
        $array2 = str_getcsv($data, ",", "\"");
        $json = json_encode($array);
        $jsondecode = json_decode($json);
       
        return [
            'data' => $json,
        ];
    }
    /**
     * Convert cvs to array when there are line breaks in content
     *
     * @param string $csvFile
     * @param int $linelen
     * @return false|array
     */
    private function csvToArray($csvFile, $linelen)
    {
        if (($contents = $csvFile) === false) {
              return false;
        }
        $fi_co = 0;
        $result = [];
        $tarray = [];
        while ($contents) {
             $word = "";
             // phpcs:ignore Squiz.Operators.IncrementDecrementUsage.NotAllowed
             $delim = (++$fi_co % $linelen) ? ',' : "\n";
             $pos = -1;
            do {
                if (($pos = strpos($contents, $delim, ++$pos)) === false) {
                    $pos = strlen($contents);
                }
                $word = substr($contents, 0, $pos);
                $x = substr_count($word, '"') % 2;
                $pos;
            } while ($x);
            if (($fi_co % $linelen) == 1) {
                $tarray = [$word];
            } else {
                $tarray[] = $word;
            }
            if ($fi_co % $linelen == 0) {
                $result[] = $tarray;
            }
            $contents = substr($contents, $pos+1);
        }
        if ($fi_co % $linelen != 0) {
            $result[] = $tarray;
        }
        return $result;
    }
}
