<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryController extends AbstractController
{
    private CategoryRepository $categoryRepository;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;
    private EntityManagerInterface $manager;

    public function __construct(CategoryRepository $categoryRepository, SerializerInterface $serializer, LoggerInterface $logger, EntityManagerInterface $manager)
    {
        $this->categoryRepository = $categoryRepository;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->manager = $manager;
    }

    // Get all categories
    #[Route('/categories', methods: ['GET'])]
    public function getCategories(): JsonResponse
    {
        try {
            $categories = $this->categoryRepository->findAll();
            $data = $this->serializer->serialize($categories, 'json');

            return new JsonResponse(
                [
                    "success" => true,
                    "message" => "Notes obtained successfully",
                    "data" => json_decode($data)
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());

            return new JsonResponse(
                [
                    "success" => false,
                    "message" => "Error obtaining the categories"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    
}
