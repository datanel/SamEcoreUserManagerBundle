<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManager;

class CustomerType extends AbstractType
{
    private $em;
    private $tokenStorage;

    public function __construct(EntityManager $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->em = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    private function initCustomerField(FormBuilderInterface $builder)
    {
        $repository = $this->em->getRepository('CanalTPSamCoreBundle:Customer');
        $user = $this->tokenStorage->getToken()->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');

        $builder->add('customer', 'entity', array(
            'label' => 'role.field.customer',
            'expanded' => false,
            'class' => 'CanalTPNmmPortalBundle:Customer',
            'property' => 'name',
            'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use ($isSuperAdmin, $user) {
                if ($isSuperAdmin) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                } else {
                    return $er->createQueryBuilder('c')
                        ->where('c.id = :custId')
                        ->setParameter('custId', $user->getCustomer()->getId())
                        ->orderBy('c.name', 'ASC');
                }
            },
            'empty_value' => ($isSuperAdmin ? 'global.please_choose' : false),
            'translation_domain' => 'messages'
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->initCustomerField($builder);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\SamEcoreUserManagerBundle\Entity\User',
                'intention'  => 'sam_user',
                'csrf_protection' => false
            )
        );
    }
}
