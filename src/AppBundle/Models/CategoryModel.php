<?php

namespace AppBundle\Models;

use AppBundle\Entity\Category;

class CategoryModel
{
    /**
     * @var Category
     */
    protected $category;

    /**
     * @var
     */
    protected $activeJobs;

    /**
     * @var
     */
    protected $moreJobs;

    /**
     * CategoryModel constructor.
     * @param Category $entity
     */
    public function __construct(Category $entity)
    {
        $this->category = $entity;
    }

    /*public function setEntity(Category $entity)
    {
        $this->category = $entity;
    }*/

    /**
     * @return Category
     */
    public function getEntity() : Category
    {
        return $this->category;
    }

    /**
     * @param $jobs
     */
    public function setMoreJobs($jobs)
    {
        $this->moreJobs = $jobs >=  0 ? $jobs : 0;
    }

    /**
     * @return mixed
     */
    public function getMoreJobs()
    {
        return $this->moreJobs;
    }

    public function setActiveJobs($jobs)
    {
        $this->activeJobs = $jobs;
    }

    public function getActiveJobs()
    {
        return $this->activeJobs;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $getter = "get" . ucfirst($name);
        if (method_exists($this->category, $getter)) {
            return $this->category->$getter();
        }
        return null;
    }
}
