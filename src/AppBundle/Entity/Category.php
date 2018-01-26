<?php

namespace AppBundle\Entity;

use AppBundle\Utils\Jobeet;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repositories\CategoryRepository")
 * @ORM\Table(name="categories")
 * @ORM\HasLifecycleCallbacks()
 */
class Category
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="Job", mappedBy="category")
     */
    private $jobs;

    /**
     * @ORM\ManyToMany(targetEntity="Affiliate", mappedBy="categories")
     */
    private $affiliates;

    private $moreJobs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->jobs = new ArrayCollection();
        $this->affiliates = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * Add job
     *
     * @param Job $job
     *
     * @return self
     */
    public function addJob(Job $job) : self
    {
        $this->jobs[] = $job;

        return $this;
    }

    /**
     * Remove job
     *
     * @param Job $job
     */
    public function removeJob(Job $job)
    {
        $this->jobs->removeElement($job);
    }

    /**
     * Get jobs
     *
     * @return Collection
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * Add affiliate
     *
     * @param Affiliate $affiliate
     *
     * @return Category
     */
    public function addAffiliate(Affiliate $affiliate) : self
    {
        $this->affiliates[] = $affiliate;

        return $this;
    }

    /**
     * Remove affiliate
     *
     * @param Affiliate $affiliate
     */
    public function removeAffiliate(Affiliate $affiliate)
    {
        $this->affiliates->removeElement($affiliate);
    }

    /**
     * Get affiliates
     *
     * @return ArrayCollection
     */
    public function getAffiliates() : ArrayCollection
    {
        return $this->affiliates;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        //$this->createdAt = new \DateTime();
        //$this->updatedAt = new \DateTime();

        $this->slug = Jobeet::slugify($this->getName());
    }

    /**
     * @return mixed
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     * @return self
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function setMoreJobs($jobs)
    {
        $this->moreJobs = $jobs >=  0 ? $jobs : 0;
    }

    public function getMoreJobs()
    {
        return $this->moreJobs;
    }
}
