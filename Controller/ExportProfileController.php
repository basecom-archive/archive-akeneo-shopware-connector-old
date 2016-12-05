<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\ImportExportBundle\Controller\ExportProfileController as BaseController;
use Pim\Bundle\ImportExportBundle\Event\JobProfileEvents;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Overrides the original ExportProfileController class to provide the JobProfile
 * edit-template with an additional argument
 *
 * Class ExportProfileController
 */
class ExportProfileController extends BaseController
{
    /**
     * Edit a job instance.
     * @AclAncestor("pim_importexport_export_profile_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->templating->renderResponse(
            'PimImportExportBundle:ExportProfile:index.html.twig',
            [
                'jobType'    => $this->getJobType(),
                'connectors' => $this->connectorRegistry->getJobs($this->getJobType())
            ]
        );
    }

    /**
     * Edit a job instance
     *
     * @AclAncestor("pim_importexport_export_profile_edit")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->request->getSession()->getFlashBag()->add('error', new Message($e->getMessage()));

            return $this->redirectToIndexView();
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::PRE_EDIT, new GenericEvent($jobInstance));

        $form = $this->formFactory->create($this->jobInstanceType, $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->entityManager->persist($jobInstance);
                $this->entityManager->flush();

                $this->eventDispatcher->dispatch(JobProfileEvents::POST_EDIT, new GenericEvent($jobInstance));

                $this->request->getSession()->getFlashBag()
                    ->add('success', new Message(sprintf('flash.%s.updated', $this->getJobType())));

                return $this->redirectToShowView($jobInstance->getId());
            }
        }

        if (null === $template = $jobInstance->getJob()->getEditTemplate()) {
            $template = 'BasecomShopwareConnectorBundle:ExportProfile:edit.html.twig';
        }
        $attributes = array_map('str_getcsv', file(__DIR__ . '/../Resources/config/additional_attributes.csv'));

        return $this->templating->renderResponse(
            $template,
            [
                'jobInstance' => $jobInstance,
                'form'        => $form->createView(),
                'attributes'  => $attributes,
            ]
        );
    }
}
