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
    private EntityManagerInterface $manager;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer, LoggerInterface $logger, UserPasswordHasherInterface  $passwordEncrypted, EntityManagerInterface $manager)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->passwordEncrypted = $passwordEncrypted;
        $this->manager = $manager;
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
    public function Register(Request $request, UserPasswordHasherInterface $passwordEncrypted, EntityManagerInterface  $manager): JsonResponse
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

            $manager->persist($newUser);
            $manager->flush();

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

    // login
    #[Route('/login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordEncrypted): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email'], $data['password'])) {
                return new JsonResponse(
                    [
                        "success" => true,
                        "message" => "Missing email or password"
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $user = $this->userRepository->findOneBy(['email' => $data['email']]);

            if (!$user || !$passwordEncrypted->isPasswordValid($user, $data['password'])) {
                return new JsonResponse(
                    [
                        "success" => true,
                        "message" => "Invalid credentials"
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $token =  bin2hex(random_bytes(32));

            return new JsonResponse(
                [
                    "success" => true,
                    "message" => "Login successfully",
                    "token" => $token

                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());

            return new JsonResponse(
                [
                    "success" => false,
                    "message" => "Error logging in"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function validateUpdate(array $data)
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

    // Update user
    #[Route('/user/{id}/update ', methods: ['PUT'])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true); 

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

            $validator = $this->validateUpdate($data);

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

            if (isset($data['username'])) {
                $user->setUsername($data['username']);
            }

            if (isset($data['email'])) {
                $existingUser = $this->userRepository->findOneBy(['email' => $data['email']]);
                if ($existingUser) {
                    return new JsonResponse(
                        [
                            "success" => true,
                            "message" => "Email invalid, try again."
                        ],
                        Response::HTTP_BAD_REQUEST
                    );
                } else {
                    $user->setEmail($data['email']); 
                }
            }
            $user->setUpdatedAt(new \DateTime());

            $this->manager->flush();

            return new JsonResponse(
                [
                    "success" => true,
                    "message" => "User updated successfully",
                    "data" => [
                        "id" => $user->getId(),
                        "username" => $user->getUsername(),
                        "email" => $user->getEmail()
                    ]
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());

            return new JsonResponse(
                [
                    "success" => false,
                    "message" => "Error updating the user"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // Delete user
    #[Route('/user/{id}/delete', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
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

            $this->manager->remove($user);
            $this->manager->flush();

            return new JsonResponse(
                [
                    "success" => true,
                    "message" => "User deleted successfully"
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());

            return new JsonResponse(
                [
                    "success" => false,
                    "message" => "Error deleting the user"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
