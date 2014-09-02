<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityRepository;;
use Doctrine\ORM\EntityManager;

class RoleByApplicationType extends AbstractType
{
    private $om;
    private $currentUserRoles;
    private $currentUserId;

    public function __construct(EntityManager $om, SecurityContext $securityContext)
    {
        $this->om = $om;
        $user = $securityContext->getToken()->getUser();
        $this->currentUserRoles = $user->getRoles();
        $this->currentUserId = $user->getId();
    }

     /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $form->add('roles', 'entity', array(
                    'label'         => $data->getName(),
                    'multiple'      => true,
                    'expanded'      => true,
                    'class'         => 'CanalTPSamCoreBundle:Role',
                    'query_builder' => function (EntityRepository $er) use ($data) {
                        $qb = $er->createQueryBuilder('r')
                            ->where('r.application = :application')
                            ->setParameter('application', $data->getId())
                            ->orderBy('r.name', 'ASC');

                        return $qb;
                    },
                    'translation_domain' => 'messages',
                    'property' => 'name'
                ));
            }
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array
(            'data_class' => 'CanalTP\SamCoreBundle\Entity\Application',
        ));
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $userEditRoles = $form->getParent()->getParent()->getData()->getRoles();

        foreach ($view->children['roles']->children as $role) {
            if (!array_key_exists($role->vars['value'], $this->currentUserRoles)) {
                $role->vars['attr']['disabled'] = 'disabled';
            }
            if (array_key_exists($role->vars['value'], $userEditRoles)) {
                $role->vars['checked'] = true;
            } else {
                $role->vars['checked'] = false;
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'assign_role_by_application';
    }
}
