<?php

namespace App\Repository;

use App\Entity\Alumnos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

/**
 * @extends ServiceEntityRepository<Alumnos>
 *
 * @method Alumnos|null find($id, $lockMode = null, $lockVersion = null)
 * @method Alumnos|null findOneBy(array $criteria, array $orderBy = null)
 * @method Alumnos[]    findAll()
 * @method Alumnos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlumnosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alumnos::class);
    }

//    /**
//     * @return Alumnos[] Returns an array of Alumnos objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Alumnos
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Definimos un método para hacer el JOIN en el repositorio
 */

 // FUNCIÓN PARA UNIR ALUMNOS Y AULAS (SEGUNDA FORMA DE HACER JOIN):
public function unirAlumnosAulas(): array {
    return $this->createQueryBuilder('a')
    // AULAS_NUM_AULA ES EL CAMPO QUE SE RELACIONA CON ALUMNOS.PHP
    // AULAS ES UN ALIAS, SE PUEDE PONER EL NOMBRE QUE QUERAMOS
        ->innerJoin("a.aulas_num_aula", "aulas")
        // ->innerJoin("a.aulas_id_asignatura", "asig") # ¡EJEMPLO!
        ->select("a.nif", "a.nombre", "a.sexo", "aulas.num_aula", "aulas.docente", "a.fechanac")
        ->orderBy("a.nombre", "ASC")
        ->getQuery()
        ->getResult()
    ;
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////

// FUNCIÓN PARA MOSTRAR SOLO MUJERES MENORES DE 30 AÑOS: 
public function consultarAlumnas(String $fecha): array {

    /*
        $objetoFecha = new DateTime($fecha);
        $constructorConsultas = $this->createQueryBuilder("a");
        $constructorConsultas
            ->where("a.sexo = :paramSexo")
            ->andWhere("a.fechanac > :paramFecha")
            ->setParameter("paramSexo", 1)
            ->setParameter("paramFecha", $objetoFecha)
            ->orderBy('a.nombre', 'DESC');
            
        return $constructorConsultas->getQuery()->getResult();
        */

        return $this->createQueryBuilder("a")
            ->where("a.sexo = :paramSexo")
            ->andWhere("a.fechanac > :paramFecha")
            ->setParameter("paramSexo", 1)
            ->setParameter("paramFecha", new DateTime($fecha))
            ->orderBy('a.nombre', 'DESC')
            ->getQuery()->getResult();
    }
}
