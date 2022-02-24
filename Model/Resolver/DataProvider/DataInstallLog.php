<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Klarna\Core\Api\Data\LogInterface;
use MagentoEse\DataInstall\Api\LoggerRepositoryInterface;
use MagentoEse\DataInstall\Api\Data\LoggerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Data Installer Log data provider
 */
class DataInstallLog
{
    /**
     * @var LoggerRepositoryInterface
     */
    private $loggerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param LoggerRepositoryInterface $loggerRepository
     * @param FilterEmulate $widgetFilter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        LoggerRepositoryInterface $loggerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->loggerRepository = $loggerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get log data by job Id
     *
     * @param string $jobId
     * @return array
     */
    public function getLogByJobId(string $jobId): array
    {
        $logData = $this->loggerRepository->getByJobId($jobId);
        return $this->formatLogData($logData, $jobId, LoggerInterface::JOBID);
    }

    /**
     * Get log data by datapack path
     *
     * @param int $datapack
     * @return array
     */
    public function getLogByDatpack(string $datapack): array
    {
        $logData = $this->loggerRepository->getByDatapack($datapack);
        return $this->formatLogData($logData, $datapack, LoggerInterface::DATAPACK);
    }

    /**
     * Formats log data for return
     *
     * @param mixed $iden$logResults
     * @param string $identifier
     * @param string $type
     * @return array
     * @throws NoSuchEntityException
     */
    private function formatLogData($logResults, $identifier, $type): array
    {
        if (empty($logResults)) {
            throw new NoSuchEntityException(
                __('The log information with %2 "%1" doesn\'t exist.', $identifier, $type)
            );
        }

        $results = [];
        foreach ($logResults as $log) {
            $results[]=[
                LoggerInterface::JOBID => $log[LoggerInterface::JOBID],
                LoggerInterface::DATAPACK => $log[LoggerInterface::DATAPACK],
                LoggerInterface::MESSAGE => $log[LoggerInterface::MESSAGE],
                LoggerInterface::LEVEL => $log[LoggerInterface::LEVEL],
                LoggerInterface::ADDDATE => $log[LoggerInterface::ADDDATE]
            ];
        }
        return $results;
    }
}
