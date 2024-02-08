<?php

namespace App\Controller;

use App\Entity\Aulas;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Importamos clases abstractas de gestión BBDD
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry; //GESTOR DE REGISTRO

#[Route('/aulas', name: 'app_aulas_')]
class AulasController extends AbstractController
{
    #[Route('/{numAula}/{capacidad}/{docente}/{hardware}', name: 'insertarAula')]
    public function index(
        int $numAula, 
        int $capacidad, 
        String $docente, 
        bool $hardware, 
        EntityManagerInterface $gestorEntidades
    ): Response {
        // endpoint de ejemplo: 
        // http://127.0.0.1:8000/aulas/23/15/Jessica Nogales/1

        // Crear objeto con la entidad
        $aula = new Aulas();
        // Voy asignando los distintos campos
        $aula->setNumAula($numAula);
        $aula->setCapacidad($capacidad);
        $aula->setDocente($docente);
        $aula->setHardware($hardware);

        // Hago el insert, persistiendo el registro
        // Persist -> Insertar objeto
        // Cierre de transacción -> FLUSH
        $gestorEntidades->persist($aula);
        $gestorEntidades->flush();

        return new Response("<h1>Registro insertado: $numAula, $capacidad, $docente, $hardware</h1>");
        /*
        return $this->render('aulas/index.html.twig', [
            'controller_name' => 'AulasController',
        ]);
        */
    }



    // Añadir MÁS DE UN REGISTRO (arrays):

    #[Route('/insertarAulas', name: 'insertarAulas')]
    public function insertarAulas(ManagerRegistry $gestorFilas) : Response 
    {
        //endpoint de ejemplo: http://127.0.0.1:8000/aulas/insertarAulas
        $gestorEntidades = $gestorFilas->getManager();

        // ARRAY BIDIMENSIONAL (array dentro de array):
        $aulas = array(
            "aula1" => array(
                // Los campos tienen que ser exactamente igual como aparece en la base de datos
                "num_aula" => 21,
                "capacidad" => 2,
                "docente" => "Isabel Álvarez",
                "hardware" => 0
            ),

            "aula2" => array(
                // Los campos tienen que ser exactamente igual como aparece en la base de datos
                "num_aula" => 22,
                "capacidad" => 15,
                "docente" => "Ignacio Mejías",
                "hardware" => 0
            )
        );


        // SE HACE UN foreach PARA LOS ARRAYS:

        foreach ($aulas as $clave => $registro) {
            $aula = new Aulas;

            // Asignamos los distintos campos
            $aula->setNumAula($registro["num_aula"]);
            $aula->setCapacidad($registro["capacidad"]);
            $aula->setDocente($registro["docente"]);
            $aula->setHardware($registro["hardware"]);

            // Hago el insert, persistiendo el registro
            // Persist -> Insertar objeto
            // Cierre de transacción -> FLUSH
            $gestorEntidades->persist($aula);
            $gestorEntidades->flush();
        }

        return new Response("<h1>Registros insertados</h1>");
    }

    // CONSULTAS (SELECT):
    #[Route('/consultarAulas', name: 'consultarAulas')]
    public function consultarAulas(ManagerRegistry $gestorFilas) : Response 
    {
        //endpoint de ejemplo: http://127.0.0.1:8000/aulas/consultarAulas
        // Saco el gestor de entidades a partir del gestor de Filas (más genérico)
        $gestorEntidades = $gestorFilas->getManager();
        // Desde el gestor de entidades, saco el repositorio de mi clase (Aulas)
        $repoAulas = $gestorEntidades->getRepository(Aulas::class);
        $filasAulas = $repoAulas->findAll();

        // PONEMOS EL TWIG PARA MOSTRAR EN HTML
        return $this->render('aulas/index.html.twig', [
            'controller_name' => 'Controlador Aulas',
            // AÑADIR LO SIGUIENTE EN INDEX.HTML.TWIG PARA MOSTRAR TABLA
            'tabla' => $filasAulas,
        ]);
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // ACTUALIZACIÓN. CRUD UPDATE
    #[Route('/actualizarAula/{numAula}/{capacidad}/{docente}/{hardware}', name: 'actualizarAula')]
    public function actualizarAula(ManagerRegistry $gestorFilas,
    $numAula, $capacidad, $docente, $hardware): Response
    {
        // endpoint de ejemplo: http://127.0.0.1:8000/aulas/actualizarAula/21/1/Isabel Álvarez Sánchez/1
            $gestorEntidades = $gestorFilas->getManager();
            $repoAulas = $gestorEntidades->getRepository(Aulas::class);
            // CAMPOS IGUAL A COMO VIENE EN EL MODELO. NUM_AULA ES POR DONDE VOY A BUSCAR:
            $arrayCriterios = ["num_aula" => $numAula];
            $aula = $repoAulas->findOneBy($arrayCriterios);
            // HASTA AQUÍ TENGO YA EL AULA QUE QUIERO MODIFICAR

        if (!$aula){
            return new Response("<p style='color:red; font-weight:bold;'>NO existe Aula con número $numAula</p>");
        }else{
            // PROCEDEMOS A LA ACTUALIZACIÓN
            // MODIFICAMOS LA CAPACIDAD, EL DOCENTE Y EL HARDWARE
            $aula->setCapacidad($capacidad);
            $aula->setDocente($docente);
            $aula->setHardware($hardware);
            $gestorEntidades->flush();

            // REDIRECCIONAR A OTRO ENDPOINT
            // Redirección entre endpoints. Usamos redirectToRoute -> Poner EL NOMBRE no la ruta
            return $this->redirectToRoute("app_aulas_consultarAulas");

            // PARA ACTUALIZAR, COGEMOS EL ENDPOINT DE ARRIBA Y PONEMOS LOS DATOS A ACTUALIZAR.
        }
    }
}
