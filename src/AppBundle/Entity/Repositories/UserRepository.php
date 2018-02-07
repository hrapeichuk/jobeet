<?php

namespace AppBundle\Entity\Repositories;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param array $attributes, where key = field name, value = field value
     * @return User
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(array $attributes)
    {
        $em = $this->getEntityManager();

        $user = new User();

        /*foreach ($attributes as $key => $value) {
            $setter = "set".ucfirst($key);
            $user->$setter($value);
        }*/
        $user->setUsername($attributes['username']);
        $user->setPassword($attributes['password']);

        $em->persist($user);
        $em->flush();

        return $user;
    }
}
