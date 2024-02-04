<?php

namespace App\Controller;

use App\Entity\Notes;
use App\Entity\User;
use App\Repository\NotesRepository;
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

class NotesController extends AbstractController
{

    private NotesRepository $notesRepository;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;
    private EntityManagerInterface $manager;

    public function __construct(NotesRepository $notesRepository, SerializerInterface $serializer, LoggerInterface $logger, EntityManagerInterface $manager)
    {
        $this->notesRepository = $notesRepository;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->manager = $manager;
    }
    // Get all notes
    #[Route('/notes', methods: ['GET'])]
    public function getNotes(): JsonResponse
    {
        try {
            $notes = $this->notesRepository->findAll();
            $data = $this->serializer->serialize($notes, 'json');

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
                    "message" => "Error obtaining the users"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // Get a note by id
    #[Route('/note/{id}', methods: ['GET'])]
    public function getNoteById(int $id): JsonResponse
    {
        try {
            $note = $this->notesRepository->find($id);

            if (!$note) {
                return new JsonResponse(
                    [
                        "success" => true,
                        "message" => "Note not found"
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }
            $data = $this->serializer->serialize($note, 'json');

            return new JsonResponse(
                [
                    "success" => true,
                    "message" => "Note obtained successfully",
                    "data" => json_decode($data)
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());

            return new JsonResponse(
                [
                    "success" => false,
                    "message" => "Error obtaining the note"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // Validate Note
    private function validateNote(array $data)
    {
        $validator = Validation::createValidator();

        $validations = new Assert\Collection([
            'user_id' =>  [
                new Assert\NotBlank(),
                new Assert\Type([
                    'type' => 'integer',
                    'message' => 'The user_id must be an integer'
                ]),
            ],
            'title' =>  [
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
            'note' =>  [
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

    // Create a note
    #[Route('/note', methods: ['POST'])]
    public function createNote(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $validator = $this->validateNote($data);

            if ($validator) {
                return new JsonResponse(
                    [
                        "success" => false,
                        "message" => "Error creating the note",
                        "errors" => $validator
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $user = $this->manager
                ->getRepository(User::class)
                ->find($data['user_id']);
 

            if (!$user) {
                return new JsonResponse(
                    [
                        "success" => false,
                        "message" => "User not found"
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }
 
            $note = new Notes();
            $note->setUser($user);
            $note->setTitle($data['title']);
            $note->setNote($data['note']);
            $note->setCreatedAt(new \DateTime());
            $note->setUpdatedAt(new \DateTime());

            $this->manager->persist($note);
            $this->manager->flush();

            return new JsonResponse(
                [
                    "success" => true,
                    "message" => "Note created successfully",
                    "data" => [
                        "id" => $note->getId(),
                        "title" => $note->getTitle(),
                        "note" => $note->getNote()]
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());

            return new JsonResponse(
                [
                    "success" => false,
                    "message" => "Error creating the note"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
