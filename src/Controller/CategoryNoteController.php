<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\CategoryNote;
use App\Entity\Notes;
use App\Repository\CategoryNoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryNoteController extends AbstractController
{
    private CategoryNoteRepository $categoryNoteRepository;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;
    private EntityManagerInterface $manager;

    public function __construct(CategoryNoteRepository $categoryNoteRepository, SerializerInterface $serializer, LoggerInterface $logger, EntityManagerInterface $manager)
    {
        $this->categoryNoteRepository = $categoryNoteRepository;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->manager = $manager;
    }

   // Add Category to Note
   #[Route('/categoryNote', methods: ['POST'])]
   public function addCategoryNote(Request $request): JsonResponse
   {
    try {
        $data = json_decode($request->getContent(), true);
 
        $category = $this->manager->getRepository(Category::class)->find($data['category']);
        $note = $this->manager->getRepository(Notes::class)->find($data['note']);

        // check if category is already added to note
        $categoryNote = $this->manager->getRepository(CategoryNote::class)->findOneBy([
            'category' => $category,
            'note' => $note,
        ]);
        
        if ($categoryNote) { 
            return new JsonResponse(
                [
                    "success" => true,
                    "message" => "Category already added to note"
                ],
                Response::HTTP_CONFLICT
            );
        }

        $categoryNote = new CategoryNote();
        $categoryNote->setCategory($category);
        $categoryNote->setNote($note); 

        $this->manager->persist($categoryNote);
        $this->manager->flush();

        return new JsonResponse(
            [
                "success" => true,
                "message" => "Category added to note",
                "data"=> $data
            ],
            Response::HTTP_CREATED
        );
    } catch (\Throwable $th) {
        $this->logger->error($th->getMessage());

        return new JsonResponse(
            [
                "success" => false,
                "message" => "Error adding category to note"
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
   }

    // Remove Category from Note
    #[Route('/categoryNote/{id}', methods: ['DELETE'])]
    public function removeCategoryNote(int $id): JsonResponse
    {
        try {
            $data = $this->categoryNoteRepository->find($id);

            if (!$data) {
                return new JsonResponse(
                    [
                        "success" => true,
                        "message" => "Category note not found"
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $this->manager->remove($data);
            $this->manager->flush();

            return new JsonResponse(
                [
                    "success" => true,
                    "message" => "Category removed from note",
                    "data"=> [
                        "id" => $id,
                        "category" => $data->getCategory()->getCategory(),
                        "note" => $data->getNote()->getId()
                    ]
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());

            return new JsonResponse(
                [
                    "success" => false,
                    "message" => "Error removing category from note"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
