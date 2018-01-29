<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Job;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class JobController
 *
 * @Route("job")
 */
class JobController extends Controller
{
    /**
     * @Route("/", name="job.index")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository(Category::class)->getWithJobs();

        return $this->render('job/index.html.twig', [
            'categories' => $this->prepareJobCategories($em, $categories)
        ]);
    }

    /**
     * @param ObjectManager $em
     * @param $categories
     * @return mixed
     */
    protected function prepareJobCategories(ObjectManager $em, $categories)
    {
        foreach ($categories as $category) {
            $category->setMoreJobs($em->getRepository(Job::class)->countActiveJobs($category->getId()) - $this->container->getParameter('max_jobs_on_homepage'));
        }

        return $categories;
    }

    /**
     * @Route("/job/{company}/{location}/{id}/{position}", name="job.show", requirements={"id" = "\d+"})
     * @ParamConverter("job", options={"repository_method" = "getActiveJob"})
     * @Method("GET")
     *
     * @param Job $job
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Job $job)
    {
        $deleteForm = $this->createDeleteForm($job);

        return $this->render('job/show.html.twig', [
            'job' => $job,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @param Category $category
     * @param $jobsPerPage
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function activeJobs(Category $category, $jobsPerPage, $page)
    {
        $em = $this->getDoctrine()->getManager();

        $activeJobs = $em->getRepository(Job::class)->getActiveJobs($category->getId(), $jobsPerPage, ($page - 1) * $jobsPerPage);

        return $this->render('job/list.html.twig', [
            'jobs' => $activeJobs,
        ]);
    }
}
