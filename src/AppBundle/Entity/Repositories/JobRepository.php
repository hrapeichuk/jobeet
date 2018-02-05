<?php

namespace AppBundle\Entity\Repositories;

use AppBundle\Entity\Job;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class JobRepository extends EntityRepository
{
    /**
     * Gets all active (not expired) jobs
     * @param int $categoryId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getActiveJobs(int $categoryId = null, int $limit = null, int $offset = null) : array
    {
        $qb = $this->createQueryBuilder('j')
            ->where('j.expiresAt > :date')
            ->setParameter('date', new \DateTime())
            ->andWhere('j.isActivated = :activated')
            ->setParameter('activated', 1)
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

    /**
     * @param null $categoryId
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countActiveJobs($categoryId = null)
    {
        $qb = $this->createQueryBuilder('j')
            ->select('count(j.id)')
            ->where('j.expiresAt > :date')
            ->setParameter('date', new \DateTime())
            ->andWhere('j.isActivated = :activated')
            ->setParameter('activated', 1);
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
    public function getActiveJob($id) : ?Job
    {
        $query = $this->createQueryBuilder('j')
            ->where('j.id = :id')
            ->setParameter('id', $id)
            ->andWhere('j.expiresAt > :date')
            ->setParameter('date', (new \DateTime()))
            ->andWhere('j.isActivated = :activated')
            ->setParameter('activated', 1)
            ->setMaxResults(1)
            ->getQuery();

        try {
            $job = $query->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            $job = null;
        }

        return $job;
    }

    /**
     * Get all expired jobs which have been expired more than 6 months ago.
     * @return array
     */
    public function getOldExpiredJobs()
    {
        return $this->createQueryBuilder('j')
            ->where('j.expiresAt < :date')
            ->setParameter('date', (new \DateTime)->modify("-6 months"))
            ->getQuery()
            ->getResult();
    }

    /**
     * Delete all non activated and old created ($days ago) jobs.
     * @param $days
     * @return mixed
     */
    public function cleanup(int $days)
    {
        $query = $this->createQueryBuilder('j')
            ->delete()
            ->where('j.isActivated IS NULL')
            ->andWhere('j.createdAt < :created_at')
            ->setParameter('created_at',  (new \DateTime())->modify(sprintf('-%d days', $days)))
            ->getQuery();
        return $query->execute();
    }

    /**
     * @param Job $job
     * @return Job
     */
    public function extend(Job $job)
    {
        if (!$job->expiresSoon()) {
            throw new \InvalidArgumentException('Can\'t extend expired job.');
        }
        $job->setExpiresAt((new \DateTime())->modify('+30 days'));
        return $job;
    }
}
