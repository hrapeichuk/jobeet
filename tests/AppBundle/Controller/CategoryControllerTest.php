<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Job;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\AppTestTrait;

class CategoryControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    public function setUp()
    {
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->refreshDatabaseSchema();

        $fixtures = [
            \AppBundle\DataFixtures\CategoryFixture::class,
            \AppBundle\DataFixtures\JobFixture::class
        ];
        $this->loadFixtures($fixtures);
    }

    /**
     * Trying to test index action of jobs where are locating the links to category.
     */
    public function testIndex()
    {
        $client = static::createClient();

        // categories on homepage are clickable
        $crawler = $client->request('GET', '/');
        $link = $crawler->selectLink('Programming')->link();
        $crawler = $client->click($link);
        $this->assertEquals('AppBundle\Controller\CategoryController::showAction', $client->getRequest()->attributes->get('_controller'));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // categories with more than $max_jobs_on_homepage jobs also have a "more" link
        $crawler = $client->request('GET', '/');
        $link = $crawler->filter('.more_jobs a')->first()->link();
        $crawler = $client->click($link);
        $this->assertEquals('AppBundle\Controller\CategoryController::showAction', $client->getRequest()->attributes->get('_controller'));
        $this->assertEquals('programming', $client->getRequest()->attributes->get('slug'));
    }

    /**
     * Test show action of category
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testShow()
    {
        $client = static::createClient();
        $max_jobs_on_category = static::$kernel->getContainer()->getParameter('max_jobs_on_category');
        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $slug = 'programming';
        $crawler = $client->request('GET', '/category/'.$slug);

        // only $max_jobs_on_category jobs are listed
        $category = $em->getRepository(Category::class)->findOneBySlug(['slug' => $slug]);
        $totalActiveJobs = $em->getRepository(Job::class)->countActiveJobs($category->getId());

        $jobsOnPage = ($totalActiveJobs <= $max_jobs_on_category) ? $totalActiveJobs : $max_jobs_on_category;
        $pages = ceil($totalActiveJobs / $max_jobs_on_category);

        $this->assertTrue($crawler->filter('.jobs tr')->count() == $jobsOnPage);
        $this->assertRegExp("/$totalActiveJobs jobs/", $crawler->filter('.pagination_desc')->text());
        $this->assertRegExp("/page 1\/$pages/", $crawler->filter('.pagination_desc')->text());

        $link = $crawler->selectLink('2')->link();
        $crawler = $client->click($link);
        $this->assertEquals(2, $client->getRequest()->attributes->get('page'));
        $this->assertRegExp("/page 2\/$pages/", $crawler->filter('.pagination_desc')->text());
    }
}
