<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//este controlador proporciona las rutas login y logout
// para que el sistema de seguridad de Symfony (firewall + authenticators) pueda funcionar
class SecurityController extends AbstractController
{
    //symfony necesita que exista GET /login (entry point), devolvemos una respuesta simple
    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return new Response('Login endpoint', Response::HTTP_OK);
        }
        return new Response('', Response::HTTP_OK);
    }

//Symfony intercepta la ruta /logout automáticamente y cierra la sesión
    #[Route(path: '/logout', name: 'app_logout', methods: ['GET','POST'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
