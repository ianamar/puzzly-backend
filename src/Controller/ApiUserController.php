<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

//propósito de este controlador es devolver información del usuario actualmente autenticado
//Si el usuario está logueado, devuelve su id, username etc
//Si no está logueado devuelve user: null.

class ApiUserController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['user' => null], 200);
        }

        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUserIdentifier(),
            ]
        ]);
    }
}
