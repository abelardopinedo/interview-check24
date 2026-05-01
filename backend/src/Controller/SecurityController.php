<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): void
    {
        // controller can be empty: it will never be called!
        // The firewall will intercept the request and handle the logout.
        throw new \Exception('This should never be reached!');
    }

    #[Route('/login_check', name: 'api_login_check')]
    public function loginCheck()
    {
        // handled by Lexik
    }
}
