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
    protected $roleByApplicationListener;
    protected $perimeterSubscriber;
    protected $securityContext;

    public function __construct($perimeterSubscriber, $securityContext)
    {
        $this->perimeterSubscriber = $perimeterSubscriber;
        $this->securityContext = $securityContext;
    }

     /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sc = $this->securityContext;
        
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($sc){

                $form = $event->getForm();
                $data = $event->getData();

                $disabledAllRoles = !$sc->isGranted('BUSINESS_MANAGE_USER_ROLE');

                $form->add('roles', 'entity', array(
                    'label'         => 'Rôles',
                    'multiple'      => true,
                    'expanded'      => true,
                    'disabled'      => $disabledAllRoles,
                    'class'         => 'CanalTPSamCoreBundle:Role',
                    'query_builder' => function (EntityRepository $er) use ($data) {
                        $qb = $er->createQueryBuilder('r')
                            ->where('r.application = :application')
                            ->andWhere('r.isEditable = true')
                            ->setParameter('application', $data->getId())
                            ->orderBy('r.name', 'ASC');

                        return $qb;
                    },
                    'translation_domain' => 'messages',
                    'property' => 'name'
                ));
                    
                $form->add('superAdmin', 'checkbox', array(
                    'label' => 'Admin ?',
                    'value' => 'superAdmin',
                    'required' => false
                ));
                    
                if (!$form->getParent()->getParent()->getData()->user->getId()) {
                    $data->setRoles(array());
                } else {
                    $apps = $form->getParent()->getParent()->getData()->applications;
                    $exists = false;
                    foreach ($apps as $app) {
                        if ($data->getId() === $app->getId()) {
                            $exists = true;
                            break;
                        }
                    }

                    if ($exists === false) {
                        $data->setRoles(array());
                    }
                }
            }
        );

        $builder->addEventSubscriber($this->perimeterSubscriber);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
//            'data_class' => 'CanalTP\SamCoreBundle\Entity\Application',
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
