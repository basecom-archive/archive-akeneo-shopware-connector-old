<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Handler;


use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;

class AssociationTypeHandler extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var AssociationTypeRepository */
    protected $associationTypeRepository;

    protected $stepExecution;

    /**
     * AssociationTypeHandler constructor.
     * @param EntityManager $entityManager
     * @param AssociationTypeRepository $associationTypeRepository
     */
    public function __construct(EntityManager $entityManager, AssociationTypeRepository $associationTypeRepository)
    {
        $this->entityManager = $entityManager;
        $this->associationTypeRepository = $associationTypeRepository;
    }

    public function execute()
    {
        if($this->associationTypeRepository->findOneByCode('similar') == null)
        {
            $similar = new AssociationType();
            $similar->setCode('similar');
            $similar->setLocale('en_US');
            $similar->setLabel('Similar');
            $this->entityManager->persist($similar);
        }
        if($this->associationTypeRepository->findOneByCode('related') == null)
        {
            $related = new AssociationType();
            $related->setCode('related');
            $related->setLocale('en_US');
            $related->setLabel('Related');
            $this->entityManager->persist($related);
        }
        $this->entityManager->flush();
        echo "Two association types added.";
        return true;
    }

    public function getConfigurationFields()
    {
        return array();
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

}