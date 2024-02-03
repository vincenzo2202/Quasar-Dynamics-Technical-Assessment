<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{

    #[Route('/check')]

    public function userList(): Response
    {
       return new Response('Hola mundo');
    }
}
