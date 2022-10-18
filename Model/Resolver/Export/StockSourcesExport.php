<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Export;

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

class StockSourcesExport implements ResolverInterface
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

        if (empty($args['sourceCode'])) {
            $filter = [];
        } else {
            $filter = ['source_code'=>$args['sourceCode'][0]];
            $filterValues = count(explode(',', $args['sourceCode'][0]));
        }

        if ($filterValues ==1) {
            $exportData = $this->singleExport('stock_sources', $filter);
        } else {
            $exportData = $this->multipleExport('stock_sources', $filter);
        }
        
        if (count($exportData) < 2) {
            throw new GraphQlNoSuchEntityException(__('No Stock Sources Found'));
        }
        $json = json_encode($exportData);
       
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

    /**
     * Export Data for a single filter or no filter
     *
     * @param string $exportType
     * @param string $filter
     * @return array
     */
    private function singleExport($exportType, $filter)
    {

        $exportInfo = $this->exportInfoFactory->create(
            'csv', //file format
            $exportType,
            $filter, //filter
            [], //skip attributes is done by attribute id, not by attribute code
            $this->localeResolver->getLocale()
        );

        $data = $this->exportManager->export($exportInfo);
        //get # of colums in header row
        $headerColumns = explode(",", explode("\n", $data)[0]);

        //fix data in the case that data elements include line feeds
        $csvCleanData = $this->csvToArray($data, count($headerColumns));

        return $csvCleanData;
    }

    /**
     * Combine multilple exports when filter only supports single entity
     *
     * @param string $exportType
     * @param array $filter
     * @return array
     */
    private function multipleExport($exportType, $filter)
    {

        $filterKey = key($filter);
        $filterValues = explode(",", $filter[$filterKey]);
        $firstExport = true;
        $totalExport = [];
        foreach ($filterValues as $singleFilter) {
            $export = $this->singleExport($exportType, [$filterKey => $singleFilter]);
            if ($firstExport) {
                $totalExport = $export;
                $firstExport = false;
            } else {
                //add export row minus header
                unset($export[0]);
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                $totalExport = array_merge($totalExport, $export);
            }
        }
 
        return $totalExport;
    }
}
