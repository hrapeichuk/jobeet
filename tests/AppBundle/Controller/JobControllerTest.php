<?php

namespace Tests\AppBundle\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class JobControllerTest extends WebTestCase
{
    public function setUp()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        if (!isset($metadatas)) {
            $metadatas = $em->getMetadataFactory()->getAllMetadata();
        }
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropDatabase();
        if (!empty($metadatas)) {
            $schemaTool->createSchema($metadatas);
        }
        $this->postFixtureSetup();

        $fixtures = [
            \AppBundle\DataFixtures\CategoryFixture::class,
            \AppBundle\DataFixtures\JobFixture::class
        ];
        $this->loadFixtures($fixtures);
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
     * Test nonexisting routes to job.
     */
    public function testShow()
    {
        $client = static::createClient();

        // a non-existent job forwards the user to a 404
        $crawler = $client->request('GET', '/job/foo-inc/milano-italy/0/painter');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        // an expired job page forwards the user to a 404
        $crawler = $client->request('GET', sprintf('/job/sensio-labs/paris-france/%d/web-developer', $this->getExpiredJob()->getId()));
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getMostRecentProgrammingJob()
    {
        $kernel = static::bootKernel();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        //$query = $em->createQuery('SELECT j from AppBundle:Job j LEFT JOIN j.category c WHERE c.slug = :slug AND j.expiresAt > :date ORDER BY j.createdAt DESC');
        $query = $em->createQueryBuilder()
            ->select('j')
            ->from(\AppBundle\Entity\Job::class, 'j')
            ->join(\AppBundle\Entity\Category::class, 'c');
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
        $kernel = static::bootKernel();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        return $em->createQueryBuilder()
            ->select('j')
            ->from(\AppBundle\Entity\Job::class, 'j')
            ->where('j.expiresAt < :date')
            ->setParameter('date', new \DateTime('-1 day'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
