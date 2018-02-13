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

        $client = $this->createJobViaForm([
            'job[email]'        => 'for.a.job@example.com',
            'job[isPublic]'     => false,
        ]);
        $this->assertEquals('AppBundle\Controller\JobController::previewAction', $client->getRequest()->attributes->get('_controller'));

        $job = $this->getEntityManager()->createQueryBuilder()
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
        static::createClient()->request('GET', '/job/new');

        $client = $this->createJobViaForm([
            'job[company]'      => 'Sensio Labs',
            'job[url]'          => null,
            'job[position]'     => 'Developer',
            'job[location]'     => 'Atlanta, USA',
            'job[description]'  => null,
            'job[howToApply]'   => null,
            'job[email]'        => 'not.an.email',
            'job[isPublic]'     => false,
        ], false);
        $crawler = $client->getCrawler();
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

    public function testPublishJob()
    {
        $client = $this->createJobViaForm(['job[position]' => 'FOO1']);
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Publish')->form();
        $client->submit($form);

        //$query = $this->getEntityManager()->createQuery('SELECT count(j.id) from AppBundle:Job j WHERE j.position = :position AND j.isActivated = 1');
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('count(j.id)')
            ->from(Job::class, 'j')
            ->where('j.position = :position')
            ->andWhere('j.isActivated = 1');
        $query->setParameter('position', 'FOO1');

        $this->assertTrue(1 == $query->getQuery()->getSingleScalarResult());
    }

    public function testDeleteJob()
    {
        $client = $this->createJobViaForm(['job[position]' => 'FOO2']);
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Delete')->form();
        $client->submit($form);

        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('count(j.id)')
            ->from(Job::class, 'j')
            ->where('j.position = :position');
        $query->setParameter('position', 'FOO2');

        $this->assertTrue(0 == $query->getQuery()->getSingleScalarResult());
    }

    public function testEditJob()
    {
        $client = $this->createJobViaForm(['job[position]' => 'FOO3']);

        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Publish')->form();
        $client->submit($form);
        $client->followRedirect();
        $client->getCrawler();

        $crawler = $client->request('GET', sprintf('/job/%s/edit', $this->getJobByPosition('FOO3')->getToken()));
        $this->assertTrue(404 === $client->getResponse()->getStatusCode());
    }

    public function testExtendJob()
    {
        // A job validity cannot be extended before the job expires soon
        $client = $this->createJobViaForm(['job[position]' => 'FOO4'], true);
        $crawler = $client->getCrawler();
        $this->assertTrue($crawler->filter('input[type=submit]:contains("Extend")')->count() == 0);

        // A job validity can be extended when the job expires soon

        // Create a new FOO5 job
        $client = $this->createJobViaForm(['job[position]' => 'FOO5'], true);
        // Get the job and change the expire date to today
        $em = $this->getEntityManager();
        $job = $em->getRepository(Job::class)->findOneByPosition('FOO5');
        $job->setExpiresAt(new \DateTime());
        $job->publish();
        $em->persist($job);
        $em->flush();

        // Go to the preview page and extend the job
        $crawler = $client->request('GET', sprintf('/job/%s/%s/%s/%s', $job->getCompanySlug(), $job->getLocationSlug(), $job->getToken(), $job->getPositionSlug()));
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Extend')->form();
        $client->submit($form);
        $client->followRedirect();

        // Reload the job from db
        $job = $this->getJobByPosition('FOO5');
        $this->getEntityManager()->refresh($job);

        // Check the expiration date
        $this->assertTrue($job->getExpiresAt()->format('y/m/d') == date('y/m/d', time() + 86400 * 30));
    }

    protected function createJobViaForm($values = [], $followRedirect = true)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/job/new');
        $form = $crawler->selectButton('Preview your job')->form(array_merge([
            'job[company]'      => 'Sensio Labs',
            'job[url]'          => 'http://www.sensio.com/',
            'job[position]'     => 'Developer',
            'job[location]'     => 'Atlanta, USA',
            'job[description]'  => 'You will work with symfony to develop websites for our customers.',
            'job[howToApply]'   => 'Send me an email',
            'job[email]'        => 'example@example.com',
            'job[isPublic]'     => false,
        ], $values));

        $client->submit($form);
        if ($followRedirect) {
            $client->followRedirect();
        }
        return $client;
    }

    protected function getJobByPosition($position)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('j')
            ->from(Job::class, 'j')
            ->where('j.position = :position');
        $query->setParameter('position', $position);
        $query->setMaxResults(1);
        return $query->getQuery()->getSingleResult();
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getMostRecentProgrammingJob()
    {
        //$query = $em->createQuery('SELECT j from AppBundle:Job j LEFT JOIN j.category c WHERE c.slug = :slug AND j.expiresAt > :date ORDER BY j.createdAt DESC');
        $query = $this->getEntityManager()->createQueryBuilder()
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
        return $this->getEntityManager()->createQueryBuilder()
            ->select('j')
            ->from(Job::class, 'j')
            ->where('j.expiresAt < :date')
            ->setParameter('date', new \DateTime('-1 day'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
