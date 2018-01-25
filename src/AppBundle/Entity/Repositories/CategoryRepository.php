<?php

namespace AppBundle\Entity\Repositories;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function getWithJobs()
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT c FROM AppBundle:Category c LEFT JOIN c.jobs j WHERE j.expiresAt > :date'
        )->setParameter('date', date('Y-m-d H:i:s', time()));

        return $query->getResult();
    }
}
