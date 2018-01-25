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
 * @Route("job")
 */
class JobController extends Controller
{
    /**
     * @Route("/", name="job_index")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository(Category::class)->getWithJobs();

        foreach ($categories as $category) {
            $category->setActiveJobs(
                $em->getRepository('AppBundle:Job')->getActiveJobs(
                    $category->getId(),
                    $this->getParameter('max_jobs_on_homepage')
                )
            );
        }

        $jobs = $em->getRepository(Job::class)->getActiveJobs();

        return $this->render('job/index.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/job/{company}/{location}/{id}/{position}", name="job_show", requirements={"id" = "\d+"})
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
}
