<?php

namespace App\Controller;

use App\Service\DirectoryAndFileRetrievalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

class GetFilesAndDirectoriesAction extends AbstractController
{
    private DirectoryAndFileRetrievalService $fileRetrievalService;

    public function __construct(DirectoryAndFileRetrievalService $fileRetrievalService)
    {
        $this->fileRetrievalService = $fileRetrievalService;
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 100;
        $files = $this->fileRetrievalService->getData($page, $limit);

        return new JsonResponse($files, Response::HTTP_OK);
    }




}