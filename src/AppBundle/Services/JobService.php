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
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var FileUploader */
    protected $fileUploader;

    /** @var Filesystem */
    protected $fileSystem;

    /** @var LoggerInterface */
    private $logger;

    /**
     * JobService constructor.
     * @param EntityManagerInterface $em
     * @param FileUploader $fileUploader
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     */
    public function __construct(EntityManagerInterface $em, FileUploader $fileUploader, LoggerInterface $logger, Filesystem $filesystem)
    {
        $this->entityManager = $em;
        $this->fileUploader = $fileUploader;
        $this->fileSystem = $filesystem;
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

        foreach ($expiredJobs as $job) {
            $em->remove($job);
            $this->deleteJobImage($job);
        }
        $em->flush();

        return count($expiredJobs);
    }

    /**
     * Deletes image of the job from the directory.
     * @param Job $job
     */
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
