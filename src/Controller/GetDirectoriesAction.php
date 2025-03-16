<?php

namespace App\Controller;

use App\Service\DirectoryRetrievalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

class GetDirectoriesAction extends AbstractController
{
    private DirectoryRetrievalService $directoryRetrievalService;

    public function __construct(DirectoryRetrievalService $fileRetrievalService)
    {
        $this->directoryRetrievalService = $fileRetrievalService;
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 100;
        $files = $this->directoryRetrievalService->getDirectories($page, $limit);

        return new JsonResponse($files, Response::HTTP_OK);
    }


}