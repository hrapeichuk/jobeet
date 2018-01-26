<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Category;
use AppBundle\Entity\Job;
use AppBundle\Entity\Repositories\JobRepository;
use Doctrine\ORM\EntityManagerInterface;

class AppExtension extends \Twig_Extension
{
    /**
     * @var JobRepository
     */
    private $jobRepository;

    /** @var string */
    private $maxJobsOnHomepage;

    /**m
     * @param EntityManagerInterface $em
     * @param string $maxJobsOnHomepage Parameter from parameters.yml
     */
    public function __construct(EntityManagerInterface $em, string $maxJobsOnHomepage)
    {
        $this->jobRepository = $em->getRepository(Job::class);
        $this->maxJobsOnHomepage = $maxJobsOnHomepage;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('getActiveCategoryJobs', [$this, 'getActiveCategoryJobs']),
        ];
    }

    /**
     * @param Category $category
     * @param int|null $maxJobs
     * @return array
     */
    public function getActiveCategoryJobs(Category $category, int $maxJobs = null)
    {
        if (!$maxJobs) {
            $maxJobs = $this->maxJobsOnHomepage;
        }
        return $this->jobRepository->getActiveJobs($category->getId(), $maxJobs);
    }
}
