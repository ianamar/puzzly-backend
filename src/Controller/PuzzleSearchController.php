<?php
namespace App\Controller;

use App\Repository\PuzzleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//Este controlador se encarga de la búsqueda de puzzles
#[Route('/api/puzzles', name: 'api_puzzles_')]
class PuzzleSearchController extends AbstractController
{
    private PuzzleRepository $repo;

    public function __construct(PuzzleRepository $repo)
    {
        $this->repo = $repo;
    }

    //expone un endpoint GET /api/puzzles/search?q=texto
    //y devuelve los puzzles cuyo nombre coincida con lo buscado

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));

        //evita hacer consultas innecesarias a la base de datos si el usuario no ha escrito nada
        if ($q === '') {
            return $this->json([], 200);
        }

        //un método definido en PuzzleRepository
        $results = $this->repo->findByNameLike($q);

        //devolver campos necesarios
        $payload = array_map(function($p) {
            return [
                'id' => $p->getId(),
                'name' => $p->getName(),
                'imagePath' => $p->getImagePath(),
                'num_rows' => $p->getNumRows(),
                'num_cols' => $p->getNumCols(),
                'category' => $p->getCategory(),
            ];
        }, $results);

        return $this->json($payload);
    }
}
