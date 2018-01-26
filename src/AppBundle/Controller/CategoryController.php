<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Job;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class JobController
 *
 * @Route("category")
 */
class CategoryController extends Controller
{
    /**
     * @Route("/", name="category.index")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /*public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository(Category::class)->getWithJobs();

        return $this->render('job/index.html.twig', [
            'categories' => $categories
        ]);
    }*/

    /**
     * @Route("/{slug}/{page}", defaults={"page": 1}, name="category.show")
     * @Method("GET")
     *
     * @param string $slug
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($slug, $page)
    {
        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository(Category::class)->findOneBy(['slug' => $slug]);
        if (!$category) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $total_jobs = $em->getRepository(Job::class)->countActiveJobs($category->getId());
        $jobs_per_page = $this->container->getParameter('max_jobs_on_category');
        $last_page = ceil($total_jobs / $jobs_per_page);
        $previous_page = $page > 1 ? $page - 1 : 1;
        $next_page = $page < $last_page ? $page + 1 : $last_page;

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'jobs_per_page' => $jobs_per_page,
            'last_page' => $last_page,
            'previous_page' => $previous_page,
            'current_page' => $page,
            'next_page' => $next_page,
            'total_jobs' => $total_jobs
        ]);
    }
}
