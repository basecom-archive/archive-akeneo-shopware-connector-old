<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\ImportExportBundle\Event\JobProfileEvents;
use Symfony\Component\EventDispatcher\GenericEvent;

use Pim\Bundle\ImportExportBundle\Controller\ExportProfileController as BaseController;

/**
 * Overrides the original ExportProfileController class to provide the JobProfile
 * edit-template with an additional argument *
 *
 * Class ExportProfileController
 * @package Basecom\Bundle\ShopwareConnectorBundle\Controller
 */
class ExportProfileController extends BaseController
{
    /**
     * Edit a job instance
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

                $this->request->getSession()->getFlashBag()
                    ->add('success', new Message(sprintf('flash.%s.updated', $this->getJobType())));

                return $this->redirectToShowView($jobInstance->getId());
            }
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::POST_EDIT, new GenericEvent($jobInstance));

        if (null === $template = $jobInstance->getJob()->getEditTemplate()) {
            $template = sprintf('PimImportExportBundle:%sProfile:edit.html.twig', ucfirst($this->getJobType()));
        }
        $attributes = array_map('str_getcsv', file(__DIR__.'/../Resources/config/additional_attributes.csv'));
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
