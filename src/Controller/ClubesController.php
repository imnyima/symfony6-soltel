<?php

namespace App\Controller;

use App\Entity\Clubes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

#[Route('/clubes', name: 'app_clubes')]
class ClubesController extends AbstractController
{
    #[Route('/insertarClubes', name: 'app_insertarClubes')]
    public function index(EntityManagerInterface $gestorEntidades): Response
    {
        //endpoint de ejemplo: http://127.0.0.1:8000/clubes/insertarClubes

        // ARRAY BIDIMENSIONAL (array dentro de array):
        $clubes = array(
            "betis" => array(
                // Los campos tienen que ser exactamente igual como aparece en la base de datos
                "cif" => "12345678A",
                "nombre" => "Real Betis",
                "fundacion" => "1907-09-12",
                "num_socios" => 65000,
                "estadio" => "Benito Villamarín"
            ),

            "sevilla" => array(
                // Los campos tienen que ser exactamente igual como aparece en la base de datos
                "cif" => "2345678B",
                "nombre" => "Sevilla FC",
                "fundacion" => "1905-10-14",
                "num_socios" => 45000,
                "estadio" => "Sánchez Pizjuan"
            )
        );

        foreach ($clubes as $registro) {
            // USAMOS TRY CATCH PARA QUE NO SE REPITA LA CLAVE PRIMARIA
            try {
                $club = new Clubes();
                $club->setCif($registro["cif"]);
                $club->setNombre($registro["nombre"]);

                // Para las fechas, creamos objeto DATETIME
                $fundacion = new DateTime($registro["fundacion"]);
                $club->setFundacion($fundacion);

                $club->setNumSocios($registro["num_socios"]);
                $club->setEstadio($registro["estadio"]);

                // Hago el insert, persistiendo el registro
                // Persist -> Insertar objeto
                // Cierre de transacción -> FLUSH
                $gestorEntidades->persist($club);
                $gestorEntidades->flush();

                // AÑADIMOS EXCEPCIÓN DE CLAVE ÚNICA (SE REPITE LA CLAVE PRIMARIA):
            } catch (UniqueConstraintViolationException $e) {
                return new Response ("<h1>¡ERROR! Clave primaria duplicada<h1>");
            }
            
        }

        return new Response ("<h1>Clubes insertados</h1>");
    }
}
