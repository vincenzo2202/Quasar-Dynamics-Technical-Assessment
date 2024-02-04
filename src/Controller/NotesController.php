<?php

namespace App\Controller;

use App\Repository\NotesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
}
