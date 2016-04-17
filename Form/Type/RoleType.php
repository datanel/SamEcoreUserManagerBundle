<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use CanalTP\SamEcoreUserManagerBundle\Form\DataTransformer\RoleToUserApplicationRoleTransformer;

class RoleType extends AbstractType
{
    private $em;
    private $userRolesTransformer;
    private $roleByApplicationType;

    public function __construct(
        EntityManager $entityManager,
        RoleToUserApplicationRoleTransformer $userRolesTransformer,
        RoleByApplicationType $roleByApplicationType
    )
    {
        $this->em = $entityManager;
        $this->userRolesTransformer = $userRolesTransformer;
        $this->roleByApplicationType = $roleByApplicationType;
    }

    private function initRoleField(FormBuilderInterface $builder)
    {
        $builder->add(
            'applications',
            'collection',
            array(
                'label' => 'applications',
                'type' => $this->roleByApplicationType
            )
        )->addModelTransformer($this->userRolesTransformer);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->initRoleField($builder);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\SamEcoreUserManagerBundle\Entity\User',
                'csrf_protection' => false
            )
        );
    }
}
