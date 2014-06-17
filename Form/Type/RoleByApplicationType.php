<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;

/**
 * Description of ApplicationRoleType
 *
 * @author akambi <contact@akambi-fagbohoun.com>
 */
class RoleByApplicationType extends AbstractType
{
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    
    protected $roleByApplicationListener;

     /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder->add('application', 'sam_role_application_perimetre_type');
        
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();
                
                $form->add('superAdmin', 'checkbox', array(
                    'label' => 'Tous les périmètres & permissions pour cette application',
                    'value' => 'superAdmin',
                    'required' => false
                ));
                    
                if (!$form->getParent()->getParent()->getData()->user->getId()) {
                    $data->application->setRoles(array());
                } else {
                    $apps = $form->getParent()->getParent()->getData()->applications;
                    $exists = false;
                    foreach ($apps as $app) {
                        
                        $userRoles = $form->getParent()->getParent()->getData()->user->getUserRoles();
                        foreach ($userRoles as $userRole) {
                            if ($userRole->getApplication()->getId() == $data->application->getId()
                                && $userRole->getCanonicalName() == self::ROLE_SUPER_ADMIN)
                            {
                                //Check or not superAdmin
                                $data->superAdmin = true;
                            }
                        }
                    }
                }
            }
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CanalTP\SamEcoreApplicationManagerBundle\Form\Model\ApplicationRolesPerimeters',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sam_role_by_application';
    }
}
