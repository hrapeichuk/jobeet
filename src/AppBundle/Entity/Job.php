<?php

namespace AppBundle\Entity;

use AppBundle\Utils\Jobeet;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repositories\JobRepository")
 * @ORM\Table(name="jobs")
 * @ORM\HasLifecycleCallbacks()
 */
class Job
{
    const TYPE_FULL_TIME = 'full-time';
    const TYPE_PART_TIME = 'part-time';
    const TYPE_FREELANCE = 'freelance';

    const TYPES = [
        self::TYPE_FULL_TIME,
        self::TYPE_PART_TIME,
        self::TYPE_FREELANCE,
    ];

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="jobs")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $location;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="text")
     */
    private $howToApply;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $token;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isPublic;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isActivated;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expiresAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type) : self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType() : ?string
    {
        return $this->type;
    }

    /**
     * Set company
     *
     * @param string $company
     *
     * @return self
     */
    public function setCompany(string $company) : self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany() : ?string
    {
        return $this->company;
    }

    /**
     * Set logo
     *
     * @param $logo
     *
     * @return self
     */
    public function setLogo($logo) : self
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return self
     */
    public function setUrl(?string $url) : self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl() : ?string
    {
        return $this->url;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return self
     */
    public function setPosition(string $position) : self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition() : ?string
    {
        return $this->position;
    }

    /**
     * Set location
     *
     * @param string $location
     *
     * @return self
     */
    public function setLocation(string $location) : self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation() : ?string
    {
        return $this->location;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription(string $description) : self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }

    /**
     * Set howToApply
     *
     * @param string $howToApply
     *
     * @return self
     */
    public function setHowToApply(string $howToApply) : self
    {
        $this->howToApply = $howToApply;

        return $this;
    }

    /**
     * Get howToApply
     *
     * @return string
     */
    public function getHowToApply() : ?string
    {
        return $this->howToApply;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return self
     */
    public function setToken(string $token) : self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken() : ?string
    {
        return $this->token;
    }

    /**
     * Set isPublic
     *
     * @param boolean $isPublic
     *
     * @return self
     */
    public function setIsPublic(?bool $isPublic) : self
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * Get isPublic
     *
     * @return boolean
     */
    public function getIsPublic() : ?bool
    {
        return $this->isPublic;
    }

    /**
     * Set isActivated
     *
     * @param boolean $isActivated
     *
     * @return self
     */
    public function setIsActivated(?bool $isActivated) : self
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    /**
     * Get isActivated
     *
     * @return boolean
     */
    public function getIsActivated() : ?bool
    {
        return $this->isActivated;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return self
     */
    public function setEmail(string $email) : self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * Set expiresAt
     *
     * @param \DateTime $expiresAt
     *
     * @return self
     */
    public function setExpiresAt(\DateTime $expiresAt) : self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * Get expiresAt
     *
     * @return \DateTime
     */
    public function getExpiresAt() : ?\DateTime
    {
        return $this->expiresAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt() : ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(\DateTime $updatedAt) : self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt() : ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

        if (!$this->getExpiresAt()) {
            $this->expiresAt = (clone $this->createdAt)->modify('+ 30 days');
        }

        if (!$this->getToken()) {
            $this->token = sha1($this->getEmail().rand(11111, 99999));
        }
    }

    /**
     * Set category
     *
     * @param Category $category
     *
     * @return self
     */
    public function setCategory(?Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category
     */
    public function getCategory() : ?Category
    {
        return $this->category;
    }

    /**
     * @return null|string|string[]
     */
    public function getCompanySlug()
    {
        return Jobeet::slugify($this->getCompany());
    }

    /**
     * @return null|string|string[]
     */
    public function getPositionSlug()
    {
        return Jobeet::slugify($this->getPosition());
    }

    /**
     * @return null|string|string[]
     */
    public function getLocationSlug()
    {
        return Jobeet::slugify($this->getLocation());
    }

    // @TODO: Refactor

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->getDaysBeforeExpires() < 0;
    }

    /**
     * @return bool
     */
    public function expiresSoon()
    {
        return $this->getDaysBeforeExpires() < 5;
    }

    /**
     * @return float
     */
    public function getDaysBeforeExpires()
    {
        return ceil(($this->getExpiresAt()->format('U') - time()) / 86400);
    }

    /**
     * Set is_activated flag to true
     */
    public function publish()
    {
        $this->setIsActivated(true);
    }

    public function extend()
    {
        if (!$this->expiresSoon()) {
            return false;
        }
        $this->expiresAt = (new \DateTime())->modify('+30 days');
        return true;
    }
}
