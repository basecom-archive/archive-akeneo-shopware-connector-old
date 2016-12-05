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
 * @package Basecom\Bundle\ShopwareConnectorBundle\Controller
 */
class ExportProfileController extends BaseController
{
    /**
     * @AclAncestor("pim_importexport_export_profile_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->templating->renderResponse(
            'PimImportExportBundle:ExportProfile:index.html.twig',
            [
                'jobType'       => $this->getJobType(),
                'connectors'    => $this->jobRegistry->allByType($this->getJobType())
            ]
        );
    }

    /**
     * Edit a job instance
     *
     * @AclAncestor("pim_importexport_export_profile_edit")
     *
     * @param Request $request
     * @param int $id
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

        $form = $this->formFactory->create($this->jobInstanceFormType, $jobInstance);

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

        $template = $this->jobTemplateProvider->getEditTemplate($jobInstance);

        $additionalAttributesPath = __DIR__ . '/../Resources/config/additional_attributes.csv';

        $attributes = file_exists($additionalAttributesPath) ? array_column(array_map('str_getcsv', file($additionalAttributesPath)), 0) : [];

        $attributes = $this->mapValuesToAttributes($attributes, $jobInstance->getRawParameters());

        return $this->templating->renderResponse(
            $template,
            [
                'jobInstance'   => $jobInstance,
                'form'          => $form->createView(),
                'attributes'    => $attributes,
            ]
        );
    }

    /**
     * @param $attributes
     * @param $values
     * @return mixed
     */
    protected function mapValuesToAttributes($attributes, $values)
    {
        if(!isset($values['attr']) || $values['attr'] === null) {
            return $attributes;
        }

        $valueArray = explode(';', $values['attr']);

        foreach ($attributes as $key => $attribute) {
            $attributeArray = explode(';', $attribute);

            foreach ($valueArray as $singleValue) {
                $value = explode(':', $singleValue);

                if ($value[0] == $attributeArray[0]) {
                    $attributes[$key] .= ';' . $value[1];
                }
            }
        }

        return $attributes;
    }
}

