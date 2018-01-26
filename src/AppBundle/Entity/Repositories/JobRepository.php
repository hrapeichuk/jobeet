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
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function getActiveJobs($categoryId = null, $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('j')
            ->where('j.expiresAt > :date')
            ->setParameter('date', new \DateTime())
            ->orderBy('j.expiresAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        if ($categoryId) {
            $qb->andWhere('j.category = :category_id')
                ->setParameter('category_id', $categoryId);
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function countActiveJobs($categoryId = null)
    {
        $qb = $this->createQueryBuilder('j')
            ->select('count(j.id)')
            ->where('j.expiresAt > :date')
            ->setParameter('date', new \DateTime());
        if ($categoryId) {
            $qb->andWhere('j.category = :category_id')
                ->setParameter('category_id', $categoryId);
        }
        $query = $qb->getQuery();

        return $query->getSingleScalarResult();
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
