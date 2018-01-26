<?php

namespace AppBundle\Entity\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class JobRepository extends EntityRepository
{
    /**
     * Gets all active (not expired) jobs
     * @param null $categoryId
     * @param null $max
     * @return array
     */
    public function getActiveJobs($categoryId = null, $max = null)
    {
        $qb = $this->createQueryBuilder('j')
            ->where('j.expiresAt > :date')
            ->setParameter('date', new \DateTime())
            ->orderBy('j.expiresAt', 'DESC');

        if ($max) {
            $qb->setMaxResults($max);
        }

        if ($categoryId) {
            $qb->andWhere('j.category = :category_id')
                ->setParameter('category_id', $categoryId);
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * @param int $id Id of the Job
     * @return mixed|null
     */
    public function getActiveJob($id)
    {
        $query = $this->createQueryBuilder('j')
            ->where('j.id = :id')
            ->setParameter('id', $id)
            ->andWhere('j.expiresAt > :date')
            ->setParameter('date', (new \DateTime()))
            ->setMaxResults(1)
            ->getQuery();

        try {
            $job = $query->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            $job = null;
        }

        return $job;
    }
}
