<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Job;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class JobAdminController extends Controller
{
    public function batchActionExtend(ProxyQueryInterface $selectedModelQuery)
    {
        if ($this->admin->isGranted('EDIT') === false || $this->admin->isGranted('DELETE') === false) {
            throw new AccessDeniedException();
        }

        $modelManager = $this->admin->getModelManager();
        $em = $this->getDoctrine()->getManager();
        $selectedModels = $selectedModelQuery->execute();

        try {
            $repo = $em->getRepository(Job::class);
            $failed = 0;
            try {
                foreach ($selectedModels as $selectedModel) {
                    $selectedModel = $repo->extend($selectedModel);
                    $modelManager->update($selectedModel);
                }
            } catch (\Exception $e) {
                $failed++;
            }
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', $e->getMessage());

            return new RedirectResponse($this->admin->generateUrl('list',$this->admin->getFilterParameters()));
        }

        $this->addFlash('sonata_flash_success', sprintf(
            '%d of %d selected jobs validity has been extended until %s.',
            count($selectedModels) - $failed,
            count($selectedModels),
            date('m/d/Y', time() + 86400 * 30))
        );

        return new RedirectResponse($this->admin->generateUrl('list',$this->admin->getFilterParameters()));
    }

    public function batchActionDeleteNeverActivated()
    {
        if ($this->admin->isGranted('EDIT') === false || $this->admin->isGranted('DELETE') === false) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $nb = $em->getRepository(Job::class)->cleanup(60);

        if ($nb) {
            $this->addFlash('sonata_flash_success',  sprintf('%d never activated jobs have been deleted successfully.', $nb));
        } else {
            $this->addFlash('sonata_flash_info',  'No jobs to delete.');
        }
        return new RedirectResponse($this->admin->generateUrl('list',$this->admin->getFilterParameters()));
    }
}
