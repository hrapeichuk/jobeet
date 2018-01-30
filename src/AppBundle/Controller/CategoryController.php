<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Job;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
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
     * @Route("/{slug}/{page}", defaults={"page": 1}, name="category.show", requirements={"page"="\d+"})
     * @Method("GET")
     * @ParamConverter("job", options={"repository_method" = "findBySlug"})
     * @param string $slug
     * @param $page
     * @param Category $category
     * @return Response
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function showAction($slug, $page, Category $category) : Response
    {
        $em = $this->getDoctrine()->getManager();

        $totalJobs = $em->getRepository(Job::class)->countActiveJobs($category->getId());
        $jobsPerPage = $this->container->getParameter('max_jobs_on_category');
        $lastPage = ceil($totalJobs / $jobsPerPage);
        $previousPage = $page > 1 ? $page - 1 : 1;
        $nextPage = $page < $lastPage ? $page + 1 : $lastPage;

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'jobsPerPage' => $jobsPerPage,
            'lastPage' => $lastPage,
            'previousPage' => $previousPage,
            'currentPage' => $page,
            'nextPage' => $nextPage,
            'totalJobs' => $totalJobs
        ]);
    }
}
