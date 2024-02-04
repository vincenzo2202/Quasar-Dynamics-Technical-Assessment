<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserControllers
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/check')]

    public function userList( ): Response
    {
 
        $response = new JsonResponse();
        $this->logger->info('list action called');
        $response->setData([
            'success' => true,
            'data' =>
            [
                'id' => 1,
                'username' => "admin",
                'email' => "admin@test.com"
            ],
            [
                'id' => 2,
                'username' => "user",
                'email' => "user@test.com"
            ]
        ]);
        return $response;
    }
}
