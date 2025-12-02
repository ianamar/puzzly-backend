<?php
namespace App\Controller;

use App\Entity\Puzzle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

//PuzzleUploadController expone un endpoint para subir imágenes y crear un nuevo puzzle en la base de datos
class PuzzleUploadController extends AbstractController
{
    //La ruta requiere que el usuario tenga ROLE_USER
    #[Route('/api/upload', name: 'upload', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function upload(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): JsonResponse
    {
        //comprueba el usuario actual
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        //lee parametros obtenidos desde el formulario, si alguno no está,
        //se asigna valor por defecto
        $name = trim((string)$request->request->get('name', 'Puzzle sin nombre'));
        $rows = (int)$request->request->get('rows', 4);
        $cols = (int)$request->request->get('cols', 4);
        $image = $request->files->get('image');

        if (!$image) {
            return $this->json(['error' => 'No image uploaded'], 400);
        }

        //Validaciones básicas
       //Extensión original en minúsculas
        $ext = strtolower($image->getClientOriginalExtension());

        //solo permitimos extensiones concretas
        $allowedExt = ['jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowedExt)) {
            return $this->json(['error' => 'Solo se permiten imágenes JPG o PNG'], 400);
        }

        //creamos un nombre seguro, sin espacios ni carácteres raros
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = $slugger->slug($originalName) . '_' . uniqid() . '.' . $ext;
        $targetDir = $this->getParameter('kernel.project_dir') . '/public/images/uploads';

        //mover el fichero subido a una carpeta de imagenes
        try {
            $image->move($targetDir, $safeName);
        } catch (\Throwable $e) {
            $this->get('logger')->error('Upload error: '.$e->getMessage(), ['exception' => $e]);
            return $this->json(['error' => 'Error guardando la imagen', 'detail' => $e->getMessage()], 500);
        }

        //crear la entidad puzzle y guardar en la bbdd
        $p = new Puzzle();
        $p->setName($name);
        $p->setNumRows($rows);
        $p->setNumCols($cols);
        $p->setImagePath('images/uploads/'.$safeName);
        $p->setUploadedBy($user);
        $p->setCategory('personal');
        $p->setCreatedAt(new \DateTimeImmutable());

        $em->persist($p);
        $em->flush();

        //construimos URL absoluta 
        $schemeAndHost = $request->getSchemeAndHttpHost();
        $imageUrl = $schemeAndHost . '/' . ltrim($p->getImagePath(), '/');

        return $this->json([
            'status' => 'ok',
            'puzzleId' => $p->getId(),
            'imageUrl' => $imageUrl,
        ], 201);
    }
}
