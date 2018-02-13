<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Job;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\AppTestTrait;

class JobControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    public function setUp()
    {
        $kernel = self::bootKernel();
        $this->setEntityManager($kernel->getContainer()
            ->get('doctrine')
            ->getManager());

        $this->refreshDatabaseSchema();

        $fixtures = [
            \AppBundle\DataFixtures\CategoryFixture::class,
            \AppBundle\DataFixtures\JobFixture::class
        ];
        $this->loadFixtures($fixtures);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->getEntityManager()->close();
        $this->setEntityManager(null); // avoid memory leaks
    }

    public function setEntityManager(?EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager() : EntityManager
    {
        return $this->em;
    }

    /**
     * Test job index page and see the job.
     */
    public function testIndex()
    {
        $client = static::createClient();
        $max_jobs_on_homepage = static::$kernel->getContainer()->getParameter('max_jobs_on_homepage');

        $crawler = $client->request('GET', '/');

        $this->assertCount(0, $crawler->filter('.jobs td.position:contains("Expired")'));

        $this->assertTrue($crawler->filter('.category_programming tr')->count() <= $max_jobs_on_homepage);
        $this->assertCount(0, $crawler->filter('.category_design .more_jobs'));
        $this->assertCount(1, $crawler->filter('.category_programming .more_jobs'));

        $job = $this->getMostRecentProgrammingJob();
        $this->assertCount(1, $crawler->filter('.category_programming tr')->first()->filter(sprintf('a[href*="/%d/"]', $job->getId())));

        $link = $crawler->selectLink('Web Developer')->first()->link();
        $crawler = $client->click($link);
        $this->assertEquals('AppBundle\Controller\JobController::showAction', $client->getRequest()->attributes->get('_controller'));
        $this->assertEquals($job->getCompanySlug(), $client->getRequest()->attributes->get('company'));
        $this->assertEquals($job->getLocationSlug(), $client->getRequest()->attributes->get('location'));
        $this->assertEquals($job->getPositionSlug(), $client->getRequest()->attributes->get('position'));
        $this->assertEquals($job->getId(), $client->getRequest()->attributes->get('id'));
    }

    /**
     * Test checks that non existing job should return 404 page.
     */
    public function testShowWithNonExistingJob()
    {
        $client = static::createClient();
        // a non-existent job forwards the user to a 404
        $crawler = $client->request('GET', '/job/foo-inc/milano-italy/0/painter');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * Test checks if expired job returns 404 page.
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testShowWithExpiredJob()
    {
        $client = static::createClient();
        // an expired job page forwards the user to a 404
        $crawler = $client->request('GET', sprintf('/job/sensio-labs/paris-france/%d/web-developer', $this->getExpiredJob()->getId()));
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * Testing job form
     */
    public function testJobForm()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/job/new');
        $this->assertEquals('AppBundle\Controller\JobController::createAction', $client->getRequest()->attributes->get('_controller'));

        $form = $crawler->selectButton('Preview your job')->form([
            'job[company]'      => 'Sensio Labs',
            'job[url]'          => 'http://www.sensio.com/',
            'job[logo]'         => __DIR__.'/../../../../../web/bundles/app/images/sensio-labs.gif',
            'job[position]'     => 'Developer',
            'job[location]'     => 'Atlanta, USA',
            'job[description]'  => 'You will work with symfony to develop websites for our customers.',
            'job[howToApply]' => 'Send me an email',
            'job[email]'        => 'for.a.job@example.com',
            'job[isPublic]'    => false,
        ]);
        $client->submit($form);
        $this->assertEquals('AppBundle\Controller\JobController::storeAction', $client->getRequest()->attributes->get('_controller'));

        $client->followRedirect();
        $this->assertEquals('AppBundle\Controller\JobController::previewAction', $client->getRequest()->attributes->get('_controller'));

        $job = $this->em->createQueryBuilder()
            ->select('j')
            ->from(Job::class, 'j')
            ->where('j.email = :email')
            ->andWhere('j.isActivated IS NULL')
            ->andWhere('j.isPublic = :isPublic')
            ->setParameters([
                'email' => 'for.a.job@example.com',
                'isPublic' => false
            ])
            ->getQuery()
            ->getSingleResult();

        $this->assertNotNull($job);
        $this->assertEquals('Atlanta, USA', $job->getLocation());
        $this->assertEquals('You will work with symfony to develop websites for our customers.', $job->getDescription());
    }

    public function testCreatingJobFormWithInvalidData()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/job/new');
        $form = $crawler->selectButton('Preview your job')->form([
            'job[company]'      => 'Sensio Labs',
            'job[position]'     => 'Developer',
            'job[location]'     => 'Atlanta, USA',
            'job[email]'        => 'not.an.email',
        ]);
        $crawler = $client->submit($form);
        $this->assertEquals('AppBundle\Controller\JobController::storeAction', $client->getRequest()->attributes->get('_controller'));
        // check if we have 3 errors
        $this->assertTrue($crawler->filter('.error_list')->count() == 3);
        // check if we have error on job_description field
        $this->assertTrue($crawler->filter('#job_description')->siblings()->first()->filter('.error_list')->count() == 1);
        // check if we have error on job_how_to_apply field
        $this->assertTrue($crawler->filter('#job_howToApply')->siblings()->first()->filter('.error_list')->count() == 1);
        // check if we have error on job_email field
        $this->assertTrue($crawler->filter('#job_email')->siblings()->first()->filter('.error_list')->count() == 1);
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getMostRecentProgrammingJob()
    {
        $kernel = static::bootKernel();

        //$query = $em->createQuery('SELECT j from AppBundle:Job j LEFT JOIN j.category c WHERE c.slug = :slug AND j.expiresAt > :date ORDER BY j.createdAt DESC');
        $query = $this->em->createQueryBuilder()
            ->select('j')
            ->from(Job::class, 'j')
            ->join(Category::class, 'c');
        $query->where('j.expiresAt > :date');
        $query->setParameter('date', new \DateTime());
        $query->andWhere('c.slug = :slug');
        $query->setParameter('slug', 'programming');
        $query->orderBy('j.createdAt', 'DESC');
        $query->setMaxResults(1);
        return $query->getQuery()->getSingleResult();
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getExpiredJob()
    {
        return $this->em->createQueryBuilder()
            ->select('j')
            ->from(\AppBundle\Entity\Job::class, 'j')
            ->where('j.expiresAt < :date')
            ->setParameter('date', new \DateTime('-1 day'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
