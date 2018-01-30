<?php

namespace AppBundle\Services;

use AppBundle\Entity\Job;
use AppBundle\Models\CategoryModel;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService
{
    protected $jobRepository;
    protected $maxJobsOnHomepage;
    private $entityManager;

    /**
     * CategoryService constructor.
     * @param EntityManagerInterface $em
     * @param string $maxJobsOnHomepage
     */
    public function __construct(EntityManagerInterface $em, string $maxJobsOnHomepage)
    {
        $this->entityManager = $em;
        $this->maxJobsOnHomepage = $maxJobsOnHomepage;
    }

    /**
     * @param $categories
     * @param null $maxActiveJobs
     * @return array
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function prepareCategoriesWithJobs($categories, $maxActiveJobs = null)
    {
        $categoriesModels = [];
        $jobRepository = $this->entityManager->getRepository(Job::class);
        if (!$maxActiveJobs) {
            $maxActiveJobs = $this->maxJobsOnHomepage;
        }

        foreach ($categories as $category) {
            $moreJobs = $jobRepository->countActiveJobs($category->getId()) - $this->maxJobsOnHomepage;
            $activeJobs = $jobRepository->getActiveJobs($category->getId(), $maxActiveJobs);

            // Model populating
            $categoryModel = new CategoryModel($category);
            $categoryModel->setMoreJobs($moreJobs);
            $categoryModel->setActiveJobs($activeJobs);
            $categoriesModels[] = $categoryModel;
        }

        return $categoriesModels;
    }
}
