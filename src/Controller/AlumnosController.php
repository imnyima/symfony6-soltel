<?php

namespace App\Controller;

use App\Entity\Alumnos;
use App\Entity\Aulas;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// PARA LAS CLAVES FORÁNEAS:
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
// PARA LAS FECHAS:
use DateTime;
// PARA JSON:
use Symfony\Component\HttpFoundation\JsonResponse;
// PARA ABAJO DEL TODO:
use App\Repository\AlumnosRepository;

#[Route('/alumnos', name: 'app_alumnos_')]
class AlumnosController extends AbstractController
{
    #[Route('/insertarAlumnos', name: 'insertarAlumnos')]
    public function index(EntityManagerInterface $gestorEntidades): Response
    {
        //endpoint de ejemplo: http://127.0.0.1:8000/alumnos/insertarAlumnos

        $alumnos = array(
            "alu1" => array(
                "nif" => "22223333J",
                "nombre" => "Hugo",
                "edad" => 30,
                "sexo" => 0,
                "fechanac" => "1994-01-10",
                "num_aula" => 23
            ),

            "alu2" => array(
                "nif" => "44445555X",
                "nombre" => "Álvaro",
                "edad" => 28,
                "sexo" => 0,
                "fechanac" => "1996-02-02",
                "num_aula" => 23
            )
        );

        $otrosAlumnos = array(
            "alu1" => array(
                "nif" => "77778888G",
                "nombre" => "José Antonio",
                "edad" => 30,
                "sexo" => 0,
                "fechanac" => "1994-01-10",
                "num_aula" => 22
            ),

            "alu2" => array(
                "nif" => "99996666H",
                "nombre" => "Marga",
                "edad" => 28,
                "sexo" => 1,
                "fechanac" => "1996-02-02",
                "num_aula" => 22
            )
        );

        // LLAMAMOS SOLO AL ARRAY OTROSALUMNOS
        foreach ($otrosAlumnos as $registro) {
            $alumno = new Alumnos();
            $alumno->setNif($registro["nif"]);
            $alumno->setNombre($registro["nombre"]);
            $alumno->setEdad($registro["edad"]);
            $alumno->setSexo($registro["sexo"]);

            $fecha = new DateTime($registro["fechanac"]);
            $alumno->setFechanac($fecha);

            // PARA CLAVE FORÁNEA
            // PRIMERO SACAMOS REPOSITORIO Y LUEGO SACAMOS REGISTRO
            $aula = $gestorEntidades->getRepository(Aulas::class)
                    ->findOneBy(["num_aula" => $registro["num_aula"]]);

            // OTRA FORMA DE HACERLO:
            /* 
            $repoAulas = $gestorEntidades->getRepository(Aulas::class);
            $paramBusqueda = ["num_aula" => $registro["num_aula"]];
            $aula = $repoAulas->findOneBy($paramBusqueda);
            */
            
            $alumno->setAulasNumAula($aula);

            // Hago el insert, persistiendo el registro
                // Persist -> Insertar objeto
                // Cierre de transacción -> FLUSH
                $gestorEntidades->persist($alumno);
                $gestorEntidades->flush();

        }

        return new Response("<h1>Insertado Alumnado</h1>");
    }

    // INSERTAR SOLO UN REGISTRO:
    /*
    #[Route('/insertar/{nif}/{nombre}/{edad}/{sexo}/{fechanac}/{numAula}', name: 'app_insertar2')]
    public function meteAlumno(
        String $nif,
        String $nombre,
        int $edad,
        bool $sexo,
        String $fechanac,
        int $numAula,
        EntityManagerInterface $gestorEntidades
    ): Response {
        // endpoint de ejemplo: 
        // http://127.0.0.1:8000/alumnos/insertar/45612378K/Juan Carlos/22/0/2001-09-16/23

        $alumno = new Alumnos();
        $alumno->setNif($nif);
        $alumno->setNombre($nombre);
        $alumno->setEdad($edad);
        $alumno->setSexo($sexo);

        $fecha = new DateTime($fechanac);
        $alumno->setFechanac($fecha);


        $aula = $gestorEntidades->getRepository(Aulas::class)
            ->findOneBy(["num_aula" => $numAula]);

        $alumno->setAulasNumAula($aula);

        $gestorEntidades->persist($alumno);
        $gestorEntidades->flush();


        return new Response("<h1>Insertado Alumnado</h1>");
    }
    */

    // SACAMOS ALUMNOS MEDIANTE UN SELECT ¡¡MEDIANTE JSON!!
    #[Route('/verAlumnos/{aula}/{sexo}', name: 'ver_alumnos')]
    public function verAlumnos(
        EntityManagerInterface $gestorEntidades,
        int $aula,
        bool $sexo
    ): Response {
        //endpoint de ejemplo: http://127.0.0.1:8000/alumnos/verAlumnos/23/0
        // SACAMOS REPOSITORIO:
        $repoAlumnos = $gestorEntidades->getRepository(Alumnos::class);
        $param = ["aulas_num_aula" => $aula, "sexo" => $sexo];
        // PARA QUE SALGA ORDENADO ASCENDENTEMENTE:
        $paramOrdenacion = ["nombre" => "ASC"];
        // AÑADIMOS LOS PARÁMETROS ANTERIORES:
        $filasAlumnos = $repoAlumnos->findBy($param, $paramOrdenacion);

        $json = array();
        foreach ($filasAlumnos as $alumno) {
            $json[] = array(
                "nif" => $alumno->getNif(),
                "nombre" => $alumno->getNombre(),
                "edad" => $alumno->getEdad()
            );
        }

        return new JsonResponse($json);
    }

    /** 
     * @todo Crear método en el repositorio para hacer el JOIN
     * @todo Endpoint que saque JOIN entre Alumnos y Aulas (Num y docente)
     * @todo Presentar datos en una tabla Bootstrap5 en twig
     */

     // UN EJEMPLO DE JOIN: PRIMER JOIN
    
     #[Route('/consultarAlumnos', name: 'consultar_alumnos_aulas')]
    public function consultarAlumnos(ManagerRegistry $gestorDoctrine){
        // CONEXIÓN A LA BASE DE DATOS:
        $conexion = $gestorDoctrine->getConnection();
        $alumnos = $conexion
            ->prepare("SELECT nif, nombre, sexo, num_aula, docente, fechanac
                       FROM aulas
                       JOIN alumnos
                       ON num_aula = aulas_num_aula")
            ->executeQuery()
            ->fetchAllAssociative();

             // $contenidoAlumnos = var_dump($alumnos);
             // return new Response("<p>$contenidoAlumnos</p>")
            // PONEMOS EL TWIG PARA MOSTRAR EN HTML
        return $this->render('alumnos/index.html.twig', [
            'controller_name' => 'Controlador Alumnos',
            'filasAlumnos' => $alumnos,
        ]);
    }    
    

    // LO MISMO QUE ARRIBA PERO EL CÓDIGO MÁS SENCILLO, SEGUNDO JOIN:
    #[Route('/consultarAlumnosAula', name: 'consultar_alumnos_aulas')]
    public function consultarAlumnosAulas(AlumnosRepository $repoAlumno){
        //endpoint: http://127.0.0.1:8000/alumnos/consultarAlumnosAulas

        $alumnos = $repoAlumno->unirAlumnosAulas();

        return $this->render('alumnos/index.html.twig', [
            'controller_name' => 'Controlador Alumnos',
            'filasAlumnos' => $alumnos,
        ]);
    }

////////////////////////////////////////////////////////////////////////////////////////
    // MOSTRAR ALUMNAS QUE SEAN MENORES QUE 30 AÑOS: 
    #[Route('/consultarAlumnas/{fecha}', name: 'consultar_alumnas')]
    public function consultarAlumnas(AlumnosRepository $repoAlumno, String $fecha): Response{
        //endpoint: http://127.0.0.1:8000/alumnos/consultarAlumnas/1990-02-07

        $alumnas = $repoAlumno->consultarAlumnas($fecha);

        return $this->render('alumnos/index.html.twig', [
            'controller_name' => 'Controlador Alumnos',
            'registrosAlumnas' => $alumnas,
        ]);
    }

////////////////////////////////////////////////////////////////////////////////////////

// BORRADO DE UN ALUMNO. CRUD DELETE
#[Route('/borrarAlumno/{nif}', name: 'borrar_alumno')]
    public function borrarAlumno(AlumnosRepository $repoAlumno, EntityManagerInterface $gestorEntidades, String $nif): Response
    {
        //endpoint: http://127.0.0.1:8000/alumnos/borrarAlumno/77778888G

        $alumno = $repoAlumno->findOneBy(["nif" => $nif]);
        $gestorEntidades->remove($alumno);
        $gestorEntidades->flush();

        // AL BORRAR, NOS REDIRIGE AL SIGUIENTE ENDPOINT (COPIAMOS Y PEGAMOS EL ENDPOINT DE ARRIBA, Y AL CARGAR LA PÁGINA NOS REDIRIGE A LA DE ABAJO):
        return $this->redirectToRoute("app_alumnos_consultar_alumnos_aulas");
    }
}