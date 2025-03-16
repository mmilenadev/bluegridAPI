<?php

namespace App\Controller;

use App\Entity\File\File;
use App\Message\DataTransferMessage;
use App\Service\DirectoryDataFormatterService;
use App\Service\FetchAndParseDataService;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class GetFilesAction extends AbstractController
{
    private FetchAndParseDataService $fileDirectoryService;

    private MessageBusInterface $bus;
    private PaginationService $paginationService;
    private DirectoryDataFormatterService $directoryDataFormatterService;

    public function __construct(PaginationService             $paginationService,
                                DirectoryDataFormatterService $directoryDataFormatterService,
                                FetchAndParseDataService      $fileDirectoryService,
                                MessageBusInterface           $bus,

    )
    {
        $this->fileDirectoryService = $fileDirectoryService;
        $this->bus = $bus;
        $this->paginationService = $paginationService;
        $this->directoryDataFormatterService = $directoryDataFormatterService;
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request): JsonResponse
    {
        $structuredData = $this->fileDirectoryService->fetchFilesAndDirectoriesData('files');
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 100;

        $files = [];

        while (empty($files)) {
            $files = $this->paginationService->paginate(File::class, $page, $limit);

            if (empty($files)) {
                $filesFromResponse = $this->fileDirectoryService->extractFiles($structuredData);
                $formattedResponse = $this->fileDirectoryService->paginate($filesFromResponse, $page, $limit);
                $this->bus->dispatch(new DataTransferMessage($structuredData));
                return new JsonResponse($formattedResponse['data'], 200);
            }
        }
        $formattedFiles = array_map(
            fn($file) => $this->directoryDataFormatterService->formatFileData($file),
            $files
        );

        return new JsonResponse($formattedFiles, 200);
    }
}