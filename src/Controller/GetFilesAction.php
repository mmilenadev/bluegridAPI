<?php

namespace App\Controller;

use App\Service\FileRetrievalService;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

class GetFilesAction extends AbstractController
{
    private FileRetrievalService $fileRetrievalService;

    public function __construct(FileRetrievalService $fileRetrievalService)
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
        $files = $this->fileRetrievalService->getFiles($page, $limit);

        return new JsonResponse($files, Response::HTTP_OK);
    }
}