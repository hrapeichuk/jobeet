<?php

namespace AppBundle\Entity\Repositories;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserRepository extends EntityRepository
{
    public function __construct(EntityManager $em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
    }

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
