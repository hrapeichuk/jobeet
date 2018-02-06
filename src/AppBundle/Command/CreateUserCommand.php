<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use AppBundle\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateUserCommand extends Command
{
    /** @var UserPasswordEncoderInterface */
    private $encoder;
    /** @var UserService */
    private $userService;

    /**
     * CategoryCommand constructor.
     * @param $name
     * @param UserService $us
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct($name = null, UserService $us, UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->userService = $us;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:user:create')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new user with input credentials.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to add new user in db...')

            ->addArgument('username', InputArgument::REQUIRED, "The name of the user.")
            ->addArgument('password', InputArgument::REQUIRED, "The password of the user.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $attributes = [
                'username' => $input->getArgument('username'),
                'password' => $input->getArgument('password'),
            ];

            $user = $this->userService->createNewUser($attributes);

            $output->writeln("<info>User successfully created (id = {$user->getId()}).</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>Something went wrong while saving new user.</error>");
            $output->writeln($e->getMessage());
        }
    }
}
