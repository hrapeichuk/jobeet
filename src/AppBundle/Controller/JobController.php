<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Job;
use AppBundle\Forms\JobType;
use AppBundle\Services\CategoryService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("job")
 */
class JobController extends Controller
{
    /**
     * @Route("/", name="job.index", methods={"GET"})
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
     * @Route("/new", name="job.create", methods={"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $job = new Job();
        $job->setType(Job::TYPE_FULL_TIME);
        $form = $this->createForm(JobType::class, $job);
        $form->handleRequest($request);

        return $this->render('job/create.html.twig', [
            'job' => $job,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/", name="job.store", methods={"POST"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function storeAction(Request $request)
    {
        $job = new Job();
        $form = $this->createForm(JobType::class, $job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush();
        }

        return $this->redirectToRoute('job.preview', [
            'token' => $job->getToken(),
            'company' => $job->getCompanySlug(),
            'location' => $job->getLocationSlug(),
            'position' => $job->getPositionSlug()
        ]);
    }

    /**
     * @Route("/{token}/edit", name="job.edit", methods={"GET"})
     *
     * @param Request $request
     * @param Job $job
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Job $job)
    {
        if ($job->getIsActivated()) {
            throw $this->createNotFoundException('Job is activated and cannot be edited.');
        }

        if (is_file($this->getParameter('jobs_directory') . '/' . $job->getLogo())) {
            $job->setLogo(new File($this->getParameter('jobs_directory') . '/' . $job->getLogo()));
        }

        $deleteForm = $this->createDeleteForm($job);

        $editForm = $this->createForm(JobType::class, $job);
        $editForm->handleRequest($request);

        return $this->render('job/edit.html.twig', [
            'job' => $job,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/{token}", name="job.update", methods={"PUT", "PATCH"})
     *
     * @param Request $request
     * @param Job $job
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, Job $job)
    {
        $editForm = $this->createForm(JobType::class, $job, ['method' => 'PUT']);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('job.preview', [
            'token' => $job->getToken(),
            'company' => $job->getCompanySlug(),
            'location' => $job->getLocationSlug(),
            'position' => $job->getPositionSlug()
        ]);
    }

    /**
     * @Route("/{token}", name="job.delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param Job $job
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Job $job)
    {
        $form = $this->createDeleteForm($job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($job);
            $em->flush();
        }

        return $this->redirectToRoute('job.index');
    }

    /**
     * @Route("/{company}/{location}/{id}/{position}", name="job.show", requirements={"id"="\d+"})
     * @Entity("job", expr="repository.getActiveJob(id)")
     * @Method("GET")
     *
     * @param Job $job
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Job $job, Request $request)
    {
        $session = $request->getSession();
        $jobsHistory = $session->get('jobs_history', []);

        $jobForHistory = $this->prepareJobAttributesForHistory($job);
        if (!in_array($jobForHistory, $jobsHistory)) {
            $newJobsHistory = [];
            $newJobsHistory[] = $jobForHistory;
            $jobsHistory = array_merge($newJobsHistory, array_slice($jobsHistory, 0, 2));
            $session->set('jobs_history', $jobsHistory);
        }

        $deleteForm = $this->createDeleteForm($job);

        return $this->render('job/show.html.twig', [
            'job' => $job,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Finds and displays the preview page for a job entity.
     *
     * @Route("/{company}/{location}/{token}/{position}", name="job.preview", requirements={"token"="\w+"})
     * @Entity("job", expr="repository.findOneByToken(token)")
     * @Method("GET")
     * @param Job $job
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function previewAction(Job $job)
    {
        $deleteForm = $this->createDeleteForm($job);
        $publishForm = $this->createPublishForm($job);

        return $this->render('job/show.html.twig', [
            'job' => $job,
            'delete_form' => $deleteForm->createView(),
            'publish_form' => $publishForm->createView(),
        ]);
    }

    /**
     * Publishes a job entity.
     *
     * @Route("/{token}/publish", name="job.publish", methods={"PUT"})
     *
     * @param Request $request
     * @param Job $job
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function publishAction(Request $request, Job $job)
    {
        $form = $this->createPublishForm($job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $job->publish();
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', 'Your job is now online for 30 days.');
        }

        return $this->redirectToRoute('job.preview', [
            'token' => $job->getToken(),
            'company' => $job->getCompanySlug(),
            'location' => $job->getLocationSlug(),
            'position' => $job->getPositionSlug()
        ]);
    }

    /**
     * @param Job $job
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createDeleteForm(Job $job)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('job.delete', ['token' => $job->getToken()]))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Creates a form to publish a job entity.
     *
     * @param Job $job The job entity
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createPublishForm(Job $job)
    {
        return $this->createFormBuilder(['token' => $job->getToken()])
            ->add('token', HiddenType::class)
            ->setMethod('PUT')
            ->getForm();
    }

    public function prepareJobAttributesForHistory(Job $job)
    {
        return [
            'id' => $job->getId(),
            'position' => $job->getPosition(),
            'company' => $job->getCompany(),
            'companySlug' => $job->getCompanySlug(),
            'locationSlug' => $job->getLocationSlug(),
            'positionSlug' => $job->getPositionSlug(),
        ];
    }
}
