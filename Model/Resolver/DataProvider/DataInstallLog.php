<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use MagentoEse\DataInstall\Api\LoggerRepositoryInterface;
use MagentoEse\DataInstall\Api\Data\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class DataInstallLog
{
    /**
     * @var LoggerRepositoryInterface
     */
    private $loggerRepository;

    /**
     * @param LoggerRepositoryInterface $loggerRepository
     */
    public function __construct(
        LoggerRepositoryInterface $loggerRepository
    ) {
        $this->loggerRepository = $loggerRepository;
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

    public function getInstalledDataPacks(): array
    {
        $logData = $this->loggerRepository->getInstalledDataPacks();
        return $this->formatLogData($logData, 'installed', LoggerInterface::DATAPACK);
    }

    /**
     * Formats log data for return
     *
     * @param mixed $logResults
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
