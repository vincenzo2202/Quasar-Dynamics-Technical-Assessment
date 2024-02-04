<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;
    private UserPasswordHasherInterface $passwordEncrypted;
    private EntityManagerInterface $em;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer, LoggerInterface $logger, UserPasswordHasherInterface  $passwordEncrypted, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->passwordEncrypted = $passwordEncrypted;
        $this->em = $em;
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

    // Get user by id
    #[Route('/user/{id}', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        try {

            $user = $this->userRepository->find($id);

            if (!$user) {
                return new JsonResponse(
                    [
                        "success" => true,
                        "message" => "User not found"
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $data = $this->serializer->serialize($user, 'json');

            return new JsonResponse(
                [
                    "success" => true,
                    "message" => "User obtained successfully",
                    "data" => json_decode($data)
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());

            return new JsonResponse(
                [
                    "success" => false,
                    "message" => "Error obtaining the user"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function validateRegister(array $data)
    {
        $validator = Validation::createValidator();

        $validations = new Assert\Collection([
            'username' =>  [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 3, 'max' => 50])
            ],
            'email' => [
                new Assert\NotBlank(),
                new Assert\Regex([
                    'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                    'message' => 'The email is not valid'
                ])
            ],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 8, 'max' => 50]),
                new Assert\Regex([
                    'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/',
                    'message' => 'Password is not valid, try again.'
                ])
            ]
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

    // Create user
    #[Route('/register', methods: ['POST'])]
    public function Register(Request $request, UserPasswordHasherInterface $passwordEncrypted, EntityManagerInterface  $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $validator = $this->validateRegister($data);

            if ($validator !== null) {
                return new JsonResponse(
                    [
                        "success" => true,
                        "message" => "Error registering user",
                        "errors" => $validator
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $newUser = new User();
            $newUser->setUsername($data['username']);
            $newUser->setEmail($data['email']);
            $newUser->setPassword($passwordEncrypted->hashPassword($newUser, $data['password']));
            $newUser->setCreatedAt(new \DateTime());
            $newUser->setUpdatedAt(new \DateTime());

            $em->persist($newUser);
            $em->flush();

            return new JsonResponse(
                [
                    "success" => true,
                    "message" => "User created successfully",
                    "data" => [
                        "id" => $newUser->getId(),
                        "username" => $newUser->getUsername(),
                        "email" => $newUser->getEmail()
                    ]
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());

            return new JsonResponse(
                [
                    "success" => false,
                    "message" => "Error creating the user"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
