<?php

namespace AppBundle\Entity\Repositories;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getWithJobs() : array
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('c.jobs', 'j')
            ->andWhere('j.expiresAt > :date')
            ->setParameter('date', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $slug
     * @return null|object
     */
    public function findBySlug($slug)
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
