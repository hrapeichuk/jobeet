<?php

namespace AppBundle\Command;

use AppBundle\Services\CategoryService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CategoryAddCommand extends Command
{
    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * CategoryCommand constructor.
     * @param $name
     * @param CategoryService $categoryService
     */
    public function __construct($name = null, CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:category:add')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new category.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to add new category in db...')

            ->addArgument('name', InputArgument::REQUIRED, "The name of the category.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->categoryService->createNewCategory($input->getArgument('name'));

            $output->writeln("<info>Category successfully created.</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>Something went wrong while saving new category.</error>");
            $output->writeln($e->getMessage());
        }
    }
}
