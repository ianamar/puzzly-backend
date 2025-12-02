<?php

namespace App\Controller;

use App\Entity\Puzzle;
use App\Entity\UserPuzzle;
use App\Repository\UserPuzzleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

//PuzzleController sirve como API REST para obtener datos de un puzzle y para guardar el progreso de un usuario

//ruta base
#[Route('/api/puzzle', name: 'api_puzzle_')]
class PuzzleController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserPuzzleRepository $userPuzzleRepo;

    public function __construct(EntityManagerInterface $em, UserPuzzleRepository $userPuzzleRepo)
    {
        $this->em = $em;
        $this->userPuzzleRepo = $userPuzzleRepo;
    }

    //GET /api/puzzle/{id} se ocupa de devolver metadata del puzzle (id, nombre, filas/columnas, ruta/URL de imagen)
    // y si hay usuario autenticado el estado guardado del usuario para ese puzzle

    #[Route('/{id}', name: 'get', methods: ['GET'], requirements: ['id' => '\d+'])]
        public function getPuzzle(Puzzle $puzzle, Request $request): JsonResponse
        {
            //obtiene el esquema y host de la petición y lo concatena con imagePath
            $schemeAndHost = $request->getSchemeAndHttpHost();
            $imageUrl = $schemeAndHost . '/' . ltrim($puzzle->getImagePath(), '/');

            //si hay usuario logueado, intenta recuperar estado en user_puzzle
            $user = $this->getUser();
            $userState = null;
            if ($user) {
                $userPuzzle = $this->userPuzzleRepo->findOneByUserAndPuzzle($user, $puzzle);
                if ($userPuzzle) {
                    $userState = $userPuzzle->getState();
                }
            }

            return $this->json([
                'id' => $puzzle->getId(),
                'name' => $puzzle->getName(),
                'imagePath' => $puzzle->getImagePath(),
                'imageUrl' => $imageUrl,
                'num_rows' => $puzzle->getNumRows(),
                'num_cols' => $puzzle->getNumCols(),
                //devolvemos el state del usuario si existe, si no -> null
                'state' => $userState ?? null,
            ]);
        }

    //POST /api/puzzle/{id} se ocupa de almacenar el estado del puzle en la tabla user_puzzle para el usuario autenticado
    #[Route('/{id}', name: 'save', methods: ['POST'])]
    public function savePuzzle(Puzzle $puzzle, Request $request): JsonResponse
    {
        //Comprobar autenticación
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['status' => 'error', 'message' => 'Authentication required'], 401);
        }

        //Leer y decodificar el body JSON
        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !array_key_exists('state', $data)) {
            return $this->json(['status' => 'error', 'message' => 'No state provided'], 400);
        }

        $state = $data['state'];

        //Buscar registro existente
        $userPuzzle = $this->userPuzzleRepo->findOneByUserAndPuzzle($user, $puzzle);

        //si no existe el registro, se crea uno nuevo
        if (!$userPuzzle) {
            $userPuzzle = new UserPuzzle();
            $userPuzzle->setUser($user);
            $userPuzzle->setPuzzle($puzzle);
            $userPuzzle->setCreatedAt(new \DateTimeImmutable());
        }

        $userPuzzle->setState(is_array($state) ? $state : null);
        $userPuzzle->setUpdatedAt(new \DateTime());

        $this->em->persist($userPuzzle);
        $this->em->flush();

        return $this->json(['status' => 'ok']);
    }
}
