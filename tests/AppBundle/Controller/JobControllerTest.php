<?php

namespace Tests\AppBundle\Controller;

use AppBundle\AppBundle;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JobControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $kernel = static::bootKernel();

        $max_jobs_on_homepage = $kernel->getContainer()->getParameter('max_jobs_on_homepage');

        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertEquals('AppBundle\Controller\JobController::indexAction', $client->getRequest()->attributes->get('_controller'));
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

        // a non-existent job forwards the user to a 404
        $crawler = $client->request('GET', '/job/foo-inc/milano-italy/0/painter');
        $this->assertTrue(404 === $client->getResponse()->getStatusCode());

        // an expired job page forwards the user to a 404
        $crawler = $client->request('GET', sprintf('/job/sensio-labs/paris-france/%d/web-developer', $this->getExpiredJob()->getId()));
        $this->assertTrue(404 === $client->getResponse()->getStatusCode());
    }

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
