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

class GetFilesAndDirectoriesAction extends AbstractController
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
        $structuredData = $this->fileDirectoryService->fetchFilesAndDirectoriesData('directories and files');

        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 100;

        $directories = $this->paginationService->paginate(Directory::class, $page, $limit);

        if (!empty($directories)) {
            $data = [];
            foreach ($directories as $directory) {
                $ipAddress = $directory->getName();
                if (!isset($data[$ipAddress])) {
                    $data[$ipAddress] = [];
                }
                $data[$ipAddress][] = $this->directoryDataFormatterService->formatDirectoryAndFileData($directory);
                $this->bus->dispatch(new DataTransferMessage($structuredData));
            }

            return new JsonResponse($data, 200);
        }
        $filesFromResponse = $this->fileDirectoryService->extractFilesAndDirectories($structuredData);
        $formattedResponse = $this->fileDirectoryService->paginate($filesFromResponse, $page, $limit);

        return new JsonResponse($formattedResponse['data'], 200);
    }




}