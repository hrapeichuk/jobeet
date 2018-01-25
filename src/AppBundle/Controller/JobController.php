<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Job;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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

        $jobs = $em->getRepository(Job::class)->findAll();

        return $this->render('job/index.html.twig', [
            'jobs' => $jobs
        ]);
    }

    /**
     * @Route("/{id}", name="job_show")
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
