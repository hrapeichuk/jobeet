<?php

namespace AppBundle\Command;

use AppBundle\Services\CategoryService;
use AppBundle\Services\JobService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearExpiredJobsCommand extends Command
{
    /**
     * @var JobService
     */
    private $jobService;

    /**
     * CategoryCommand constructor.
     * @param $name
     * @param JobService $jobService
     */
    public function __construct($name = null, JobService $jobService)
    {
        $this->jobService = $jobService;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:jobs:clear-old-expired')

            // the short description shown while running "php bin/console list"
            ->setDescription('Deletes old expired jobs which are older than 6 months.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to clear old expired jobs.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $number = $this->jobService->clearOldExpiredJobs();

            $output->writeln("<info>Cleared $number old expired jobs.</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>Something went wrong while deleting old expired jobs.</error>");
            $output->writeln($e->getMessage());
        }
    }
}
