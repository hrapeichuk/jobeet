<?php

namespace AppBundle\Models;

use AppBundle\Entity\Category;

class CategoryModel
{
    /** @var Category */
    protected $category;

    /** @var */
    private $id;

    /** @var */
    private $name;

    /** @var */
    private $slug;

    /** @var */
    private $jobs;

    /** @var */
    private $affiliates;

    /** @var */
    protected $activeJobs;

    /** @var */
    protected $moreJobs;

    /**
     * CategoryModel constructor.
     * @param Category $entity
     */
    public function __construct(Category $entity)
    {
        $this->category = $entity;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return mixed
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * @param mixed $jobs
     */
    public function setJobs($jobs): void
    {
        $this->jobs = $jobs;
    }

    /**
     * @return mixed
     */
    public function getAffiliates()
    {
        return $this->affiliates;
    }

    /**
     * @param mixed $affiliates
     */
    public function setAffiliates($affiliates): void
    {
        $this->affiliates = $affiliates;
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

    /**
     * @param $jobs
     */
    public function setActiveJobs($jobs)
    {
        $this->activeJobs = $jobs;
    }

    /**
     * @return mixed
     */
    public function getActiveJobs()
    {
        return $this->activeJobs;
    }
}
