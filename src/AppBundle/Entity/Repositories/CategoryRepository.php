<?php

namespace AppBundle\Entity\Repositories;

use AppBundle\Entity\Category;
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
            ->andWhere('j.isActivated = :activated')
            ->setParameter('activated', 1)
            ->getQuery()
            ->getResult();
    }

    public function create(string $name)
    {
        $entityClass = $this->getEntityName();
        /**
         * @var Category $category
         */
        $category = (new $entityClass);
        $category->setName($name);

        $em = $this->getEntityManager();
        $em->persist($category);
        $em->flush();

        return $category;
    }
}
