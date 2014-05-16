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
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();

                $disabledAllRoles = !$this->securityContext->isGranted('BUSINESS_MANAGE_USER_ROLE');
                
                $form->add('roles', 'entity', array(
                    'label'         => 'Rôles',
                    'multiple'      => true,
                    'expanded'      => true,
                    'disabled'      => $disabledAllRoles,
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
            'data_class' => 'CanalTP\SamCoreBundle\Entity\Application',
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
