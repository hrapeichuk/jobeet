<?php

namespace AppBundle\Command;

use AppBundle\Services\CategoryService;
use AppBundle\Services\JobService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearOldNonActivatedJobsCommand extends Command
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
            ->setName('app:jobs:clear-old-nonactivated')

            // the short description shown while running "php bin/console list"
            ->setDescription('Deletes old non activated jobs which are older than x days.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to clear old non activated jobs.')

            ->addArgument('days', InputArgument::OPTIONAL, "Jobs older than x days.", 90);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $days = $input->getArgument('days');
            $number = $this->jobService->clearOldNonActivatedJobs($days);

            $output->writeln("<info>Cleared $number old nonactivated jobs.</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>Something went wrong while deleting old nonactivated jobs.</error>");
            $output->writeln($e->getMessage());
        }
    }
}
