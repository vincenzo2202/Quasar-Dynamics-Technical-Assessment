<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    // Get all users 
    #[Route('/users', methods: ['GET'])]
    public function getAllUsers(): JsonResponse
    {
        try {
            $users = $this->userRepository->findAll();

            $data = $this->serializer->serialize($users, 'json');

            return new JsonResponse(
                [
                    "success" =>  true,
                    "message" => "Users obtained successfully",
                    "data" => json_decode($data),
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
}
