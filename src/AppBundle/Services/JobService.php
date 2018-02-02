<?php

namespace AppBundle\Services;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Entity\Job;
use AppBundle\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;

class JobService
{
    protected $entityManager;
    /**
     * @var FileUploader
     */
    protected $fileUploader;
    protected $fileSystem;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManagerInterface $em, FileUploader $fileUploader, LoggerInterface $logger)
    {
        $this->entityManager = $em;
        $this->fileUploader = $fileUploader;
        $this->fileSystem = new Filesystem();
        $this->logger = $logger;
    }

    /**
     * Clears jobs where expires_at date is older half of year from current.
     */
    public function clearOldExpiredJobs()
    {
        $em = $this->entityManager;
        $repository = $em->getRepository(Job::class);
        $expiredJobs = $repository->getOldExpiredJobs();

        $number = 0;
        foreach ($expiredJobs as $job) {
            $em->remove($job);
            $this->deleteJobImage($job);
            $number++;
        }
        $em->flush();

        return $number;
    }

    public function deleteJobImage(Job $job)
    {
        $file = $this->fileUploader->getTargetDir()."/".$job->getLogo();
        try {
            if ($this->fileSystem->exists($file)) {
                $this->fileSystem->remove($file);
            }
        } catch (IOException $e) {
            $this->logger->error('An error occurred: '.$e->getMessage());
        }
    }
}
