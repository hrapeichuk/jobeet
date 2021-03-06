<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Job;
use AppBundle\Services\CategoryService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
     * @Entity("job", expr="repository.findBySlug(slug)")
     *
     * @param $page
     * @param Category $category
     * @param CategoryService $categoryService
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function showAction($page, Category $category, CategoryService $categoryService) : Response
    {
        $em = $this->getDoctrine()->getManager();

        $totalJobs = $em->getRepository(Job::class)->countActiveJobs($category->getId());
        $jobsPerPage = $this->container->getParameter('max_jobs_on_category');
        $lastPage = ceil($totalJobs / $jobsPerPage);
        $previousPage = $page > 1 ? $page - 1 : 1;
        $nextPage = $page < $lastPage ? $page + 1 : $lastPage;

        $categoryModel = $categoryService->getCategoryModel($category);
        $activeJobs = $em->getRepository(Job::class)->getActiveJobs($category->getId(), $jobsPerPage, ($page - 1) * $jobsPerPage);
        $categoryModel->setActiveJobs($activeJobs);

        return $this->render('category/show.html.twig', [
            'category' => $categoryModel,

            'lastPage' => $lastPage,
            'previousPage' => $previousPage,
            'currentPage' => $page,
            'nextPage' => $nextPage,
            'totalJobs' => $totalJobs
        ]);
    }
}
