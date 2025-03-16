<?php

namespace App\Controller;

use App\Entity\Directory\Directory;
use App\Message\DataTransferMessage;
use App\Service\DirectoryDataFormatterService;
use App\Service\FetchAndParseDataService;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class GetDirectoriesAction extends AbstractController
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
        $structuredData = $this->fileDirectoryService->fetchFilesAndDirectoriesData('directories');
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 100;
        $directories = [];

        while (empty($directories)) {
            $directories = $this->paginationService->paginate(Directory::class, $page, $limit);
            if (empty($directories)) {
                $filesFromResponse = $this->fileDirectoryService->extractDirectories($structuredData);
                $formattedResponse = $this->fileDirectoryService->paginate($filesFromResponse, $page, $limit);
                $this->bus->dispatch(new DataTransferMessage($structuredData));
                return new JsonResponse($formattedResponse['data'], 200);
            }
        }
        $formattedDirectories = array_map(fn(Directory $directory) => $this->directoryDataFormatterService->formatDirectoryData($directory),
            $directories);
        return new JsonResponse($formattedDirectories, 200);


    }

}