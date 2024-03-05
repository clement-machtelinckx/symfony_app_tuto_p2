<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityController extends AbstractController
{
    /**
     * this fonction is use to manage the login part 
     */
    #[Route('/login', name:'app_login', methods: ['POST'])]
    public function login(#[CurrentUser] User $user=null): Response
    {
        if(!$user) {
            return $this->json([
                'error' => 'Invalid login request: check that the content-type header is application/json',
            ], 401);
        }
        return $this->json([
            'user' => $user->getId(),
        ]);
    }
}