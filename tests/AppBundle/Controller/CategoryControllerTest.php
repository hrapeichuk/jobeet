<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    public function testShow()
    {
        // get the custom parameters from app config.yml
        $kernel = static::bootKernel();
        $max_jobs_on_homepage = $kernel->getContainer()->getParameter('max_jobs_on_homepage');
        $max_jobs_on_category = $kernel->getContainer()->getParameter('max_jobs_on_category');
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $client = static::createClient();

        // categories on homepage are clickable
        $crawler = $client->request('GET', '/');
        $link = $crawler->selectLink('Programming')->link();
        $crawler = $client->click($link);
        $this->assertEquals('AppBundle\Controller\CategoryController::showAction', $client->getRequest()->attributes->get('_controller'));
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $this->assertEquals('programming', $client->getRequest()->attributes->get('slug'));

        // categories with more than $max_jobs_on_homepage jobs also have a "more" link
        $crawler = $client->request('GET', '/');
        //$link = $crawler->selectLink('22')->link();
        $link = $crawler->filter('.more_jobs a')->first()->link();
        $crawler = $client->click($link);
        $this->assertEquals('AppBundle\Controller\CategoryController::showAction', $client->getRequest()->attributes->get('_controller'));
        $this->assertEquals('programming', $client->getRequest()->attributes->get('slug'));

        // only $max_jobs_on_category jobs are listed
        $category = $em->getRepository(Category::class)->findOneBySlug(['slug' => 'programming']);
        $totalActiveJobs = $em->getRepository(Job::class)->countActiveJobs($category->getId());

        $jobsOnPage = ($totalActiveJobs <= $max_jobs_on_category) ? $totalActiveJobs : $max_jobs_on_category;

        $this->assertTrue($crawler->filter('.jobs tr')->count() == $jobsOnPage);
        $this->assertRegExp("/$totalActiveJobs jobs/", $crawler->filter('.pagination_desc')->text());
        $this->assertRegExp('/page 1\/2/', $crawler->filter('.pagination_desc')->text());

        $link = $crawler->selectLink('2')->link();
        $crawler = $client->click($link);
        $this->assertEquals(2, $client->getRequest()->attributes->get('page'));
        $this->assertRegExp('/page 2\/2/', $crawler->filter('.pagination_desc')->text());
    }
}
