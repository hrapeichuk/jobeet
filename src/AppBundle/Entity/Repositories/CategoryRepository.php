<?php

namespace AppBundle\Entity\Repositories;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function getWithJobs()
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('c.jobs', 'j')
            ->andWhere('j.expiresAt > :date')
            ->setParameter('date', new \DateTime())
            ->getQuery()
            ->getResult();
    }
}
