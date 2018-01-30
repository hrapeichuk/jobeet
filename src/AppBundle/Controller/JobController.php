<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Job;
use AppBundle\Models\CategoryModel;
use AppBundle\Services\CategoryService;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("job")
 */
class JobController extends Controller
{
    /**
     * @Route("/", name="job.index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository(Category::class)->getWithJobs();

        return $this->render('job/index.html.twig', [
            'categories' => $this->get(CategoryService::class)->prepareCategoriesWithJobs($categories)
        ]);
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
}
