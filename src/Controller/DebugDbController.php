<?php
// src/Controller/DebugDbController.php
namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DebugDbController extends AbstractController
{
    #[Route('/_debug/db', name: 'debug_db', methods: ['GET'])]
    public function debugDb(Connection $conn): JsonResponse
    {
        $params = $conn->getParams();

        // listar todas las tablas que contengan 'puzz'
        try {
            $tables = $conn->fetchFirstColumn("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE '%puzz%'");
        } catch (\Throwable $e) {
            $tables = ['error' => $e->getMessage()];
        }

        // intentar DESCRIBE para 'puzzle' y 'puzzles' (si existen)
        $desc = [];
        foreach (['puzzle', 'puzzles'] as $t) {
            try {
                $desc[$t] = $conn->executeQuery("DESCRIBE `$t`")->fetchAllAssociative();
            } catch (\Throwable $e) {
                $desc[$t] = ['error' => $e->getMessage()];
            }
        }

        return $this->json([
            'connection_params' => $params,
            'tables_like_puzz' => $tables,
            'describe' => $desc,
        ]);
    }
}
