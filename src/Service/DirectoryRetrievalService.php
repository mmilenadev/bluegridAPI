<?php

namespace App\Service;

use App\Entity\Directory\Directory;
use App\Message\DataTransferMessage;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DirectoryRetrievalService
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
    public function getDirectories(int $page, int $limit): array
    {
        $directories = $this->paginationService->paginate(Directory::class, $page, $limit);
        if (empty($directories)) {
            $structuredData = $this->dataService->fetchFilesAndDirectoriesData('directories');
            $filesFromResponse = $this->dataService->extractDirectories($structuredData);
            $formattedResponse = $this->dataService->paginate($filesFromResponse, $page, $limit);
            $this->bus->dispatch(new DataTransferMessage($structuredData));

            return $formattedResponse['data'];
        }

        return array_map(fn(Directory $directory) => $this->formatterService->formatDirectoryData($directory),
            $directories);
    }
}

