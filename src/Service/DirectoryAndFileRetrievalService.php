<?php

namespace App\Service;

use App\Entity\Directory\Directory;
use App\Message\DataTransferMessage;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DirectoryAndFileRetrievalService
{
    private FetchAndParseDataService $dataService;
    private PaginationService $paginationService;
    private DirectoryDataFormatterService $formatterService;
    private MessageBusInterface $bus;

    public function __construct(
        FetchAndParseDataService      $dataService,
        PaginationService             $paginationService,
        DirectoryDataFormatterService $formatterService,
        MessageBusInterface           $bus
    )
    {
        $this->dataService = $dataService;
        $this->paginationService = $paginationService;
        $this->formatterService = $formatterService;
        $this->bus = $bus;
    }

    /**
     *
     * @param int $page
     * @param int $limit
     * @return array
     * @throws ExceptionInterface
     */
    public function getData(int $page, int $limit): array
    {
        $structuredData = $this->dataService->fetchFilesAndDirectoriesData('directories and files');
        $directories = $this->paginationService->paginate(Directory::class, $page, $limit);
        if (!empty($directories)) {
            $groupedData = [];
            foreach ($directories as $directory) {
                $groupKey = $directory->getName();
                if (!isset($groupedData[$groupKey])) {
                    $groupedData[$groupKey] = [];
                }
                $groupedData[$groupKey][] = $this->formatterService->formatDirectoryAndFileData($directory);
            }

            return $groupedData;
        }
        $filesAndDirectories = $this->dataService->extractFilesAndDirectories($structuredData);
        $paginatedResponse = $this->dataService->paginate($filesAndDirectories, $page, $limit);
        $this->bus->dispatch(new DataTransferMessage($structuredData));
        return $paginatedResponse['data'];
    }

}

