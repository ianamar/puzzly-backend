<?php
namespace App\Controller;

use App\Entity\Puzzle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

//Este controlador expone un endpoint de la API que devuelve todos los puzzles de una categoría concreta
class ApiPuzzleController extends AbstractController
{
    #[Route('/api/category/{slug}/puzzles', name: 'api_category_puzzles', methods: ['GET'])]
    public function puzzlesByCategory(EntityManagerInterface $em, Request $request, string $slug): JsonResponse
    {
        //consulta a la tabla/entidad Puzzle
        $repo = $em->getRepository(Puzzle::class);
        $puzzles = $repo->findBy(['category' => $slug], ['createdAt' => 'DESC']);

        //obtener la ruta base de servidor
        $baseUrl = $request->getSchemeAndHttpHost();

        //transforma cada puzzle en un array con sus datos
        $data = array_map(function(Puzzle $p) use ($baseUrl) {

            //construir URL absoluta
            $imagePath = ltrim($p->getImagePath(), '/');
            $imageUrl = $baseUrl . '/' . $imagePath;

            //devolvemos datos de puzles para poder mostrar sus nombres, cantidad de piezas etc.
            return [
                'id'        => $p->getId(),
                'name'      => $p->getName(),
                'image_path'=> $p->getImagePath(),
                'imageUrl'  => $imageUrl,       
                'num_rows'  => $p->getNumRows(),
                'num_cols'  => $p->getNumCols(),
            ];
        }, $puzzles);

        return $this->json($data);
    }
}
