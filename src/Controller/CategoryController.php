<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

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

    // Get a category by id
    #[Route('/category/{id}', methods: ['GET'])]
    public function getCategory(int $id): JsonResponse
    {
        try {
            $category = $this->categoryRepository->find($id);

            if (!$category) {
                return new JsonResponse(
                    [
                        "success" => true,
                        "message" => "Category not found"
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }
            $data = $this->serializer->serialize($category, 'json');

            return new JsonResponse(
                [
                    "success" => true,
                    "message" => "Category obtained successfully",
                    "data" => json_decode($data)
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());

            return new JsonResponse(
                [
                    "success" => false,
                    "message" => "Error obtaining the category"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // Validate the category
    private function validateCategory(array $data)
    {
        $validator = Validation::createValidator();

        $validations = new Assert\Collection([
            'category' =>  [
                new Assert\NotBlank(),
                new Assert\Type([
                    'type' => 'string',
                    'message' => 'The title must be a string'
                ]),
                new Assert\Length([
                    'min' => 3,
                    'max' => 100,
                    'maxMessage' => 'The title cannot be longer than {{ 100 }} characters'
                ]),
                new Assert\Regex([
                    'pattern' => '/\S/',
                    'message' => 'The title must contain letters or numbers'
                ]),
            ],
            'description' =>  [
                new Assert\NotBlank(),
                new Assert\Type([
                    'type' => 'string',
                    'message' => 'The note must be a string'
                ]),
                new Assert\Length([
                    'min' => 3,
                    'max' => 255,
                    'maxMessage' => 'The note cannot be longer than {{ 255 }} characters'
                ]),
                new Assert\Regex([
                    'pattern' => '/\S/',
                    'message' => 'The note must contain letters or numbers'
                ]),
            ],
        ]);

        $violations = $validator->validate($data, $validations);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            return $errors;
        }

        return null;
    }

    // Create a category
    #[Route('/category/create', methods: ['POST'])]
    public function createNote(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $validator = $this->validateCategory($data);


            if ($validator) {
                return new JsonResponse(
                    [
                        "success" => true,
                        "message" => "Invalid data",
                        "errors" => $validator
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $existingCategory = $this->manager
                ->getRepository(Category::class)
                ->findOneBy(['category' => $data["category"]]);

            if ($existingCategory) {
                return new JsonResponse(
                    [
                        "success" => true,
                        "message" => "Category already exists"
                    ],
                    Response::HTTP_CONFLICT
                );
            }

            $new = new Category();
            $new->setCategory($data["category"]);
            $new->setDescription($data["description"]);
            $new->setCreatedAt(new \DateTime());
            $new->setUpdatedAt(new \DateTime());

            $this->manager->persist($new);
            $this->manager->flush();

            return new JsonResponse(
                [
                    "success" => true,
                    "message" => "Category created successfully",
                    "data" => $data
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());

            return new JsonResponse(
                [
                    "success" => false,
                    "message" => "Error creating the category"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
