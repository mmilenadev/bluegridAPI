<?php

namespace App\Service;

use App\Entity\File\File;
use App\Message\DataTransferMessage;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class FileRetrievalService
{
    private FetchAndParseDataService $dataService;
    private PaginationService $paginationService;
    private DirectoryDataFormatterService $formatterService;
    private MessageBusInterface $bus;

    public function __construct(
        FetchAndParseDataService $dataService,
        PaginationService $paginationService,
        DirectoryDataFormatterService $formatterService,
        MessageBusInterface $bus
    ) {
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
    public function getFiles(int $page, int $limit): array
    {
        $files = $this->paginationService->paginate(File::class, $page, $limit);
        if (empty($files)) {
            $structuredData = $this->dataService->fetchFilesAndDirectoriesData('files');
            $filesFromResponse = $this->dataService->extractFiles($structuredData);
            $paginatedResponse = $this->dataService->paginate($filesFromResponse, $page, $limit);
            $this->bus->dispatch(new DataTransferMessage($structuredData));

            return $paginatedResponse['data'];
        }

        return array_map(
            fn($file) => $this->formatterService->formatFileData($file),
            $files
        );
    }
}

