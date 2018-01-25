<?php

namespace AppBundle\Entity\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class JobRepository extends EntityRepository
{
    public function getActiveJobs($categoryId = null, $max = null)
    {
        $qb = $this->createQueryBuilder('j')
            ->where('j.expiresAt > :date')
            ->setParameter('date', date('Y-m-d H:i:s', time()))
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

    public function getActiveJob($id)
    {
        $query = $this->createQueryBuilder('j')
            ->where('j.id = :id')
            ->setParameter('id', $id)
            ->andWhere('j.expiresAt > :date')
            ->setParameter('date', date('Y-m-d H:i:s', time()))
            ->setMaxResults(1)
            ->getQuery();

        try {
            $job = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException | NonUniqueResultException $e) {
            $job = null;
        }

        return $job;
    }
}
