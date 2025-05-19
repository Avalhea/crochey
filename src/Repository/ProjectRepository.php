<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * Search projects by keyword including tag labels
     * 
     * @param string $query The search query
     * @return Project[] Returns an array of Project objects
     */
    public function searchByNameDescriptionOrTag(string $query): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.tags', 't');

        // Split the query into keywords
        $keywords = explode(' ', $query);
        
        foreach ($keywords as $index => $keyword) {
            $paramName = 'keyword_' . $index;
            $likePattern = '%' . $keyword . '%';
            
            // Search in project name, description OR tag label
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('p.name', ':' . $paramName),
                    $qb->expr()->like('p.description', ':' . $paramName),
                    $qb->expr()->like('t.label', ':' . $paramName)
                )
            )
            ->setParameter($paramName, $likePattern);
        }

        return $qb
            ->distinct(true)  // Prevent duplicate projects
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Project[] Returns an array of Project objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Project
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
