<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var LoggerInterface */
    private $logger;
    /** @var UserPasswordEncoderInterface */
    private $encoder;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, UserPasswordEncoderInterface $encoder)
    {
        $this->entityManager = $em;
        $this->logger = $logger;
        $this->encoder = $encoder;
    }

    public function createNewUser(array $attributes)
    {
        if (isset($attributes['password'])) {
            $attributes['password'] = $this->encoder->encodePassword(new User, $attributes['password']);
        }
        return $this->entityManager->getRepository(User::class)->create($attributes);
    }
}
