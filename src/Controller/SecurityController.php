<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use ApiPlatform\Api\IriConverterInterface;
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
    public function login(IriConverterInterface  $iriConverter, #[CurrentUser] User $user=null): Response
    {
        if(!$user) {
            return $this->json([
                'error' => 'Invalid login request: check that the content-type header is application/json',
            ], 401);
        }
        return new Response(null, 204, [
            'Location' => $iriConverter->getIriFromResource($user)
        ]);
    }

    #[Route('/logout', name:'app_logout')]
    public function logout(): void
    {
        throw new Exception('This should never be reached!');
    }
}