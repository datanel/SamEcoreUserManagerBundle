<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\EventListener;

use CanalTP\SamEcoreUserManagerBundle\Form\Model\UserRegistration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Role\Role;

class RegistrationSuscriber implements EventSubscriberInterface
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Defini les methodes associés aux evenements
     *
     * @return Array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::POST_SET_DATA => 'postSetData',
        );
    }

    /**
     * Fonction appelée lors de l'evenement FormEvents::PRE_SET_DATA
     *
     * @param \Symfony\Component\Form\Event\FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($data instanceof UserRegistration) {
            $applications = $this->em->getRepository('CanalTPSamCoreBundle:Application')->findAllOrderedByName();
            $data->rolesAndPerimetersByApplication = $applications;

            // $form->add(
            //     'applications',
            //     'choice',
            //     array(
            //         'label'       => 'role.field.application',
            //         'multiple'    => true,
            //         'expanded'    => true,
            //         'required'    => false,
            //         'choice_list' => new ObjectChoiceList($applications, 'name')
            //     )
            // );
            $form->add('applications', 'entity', array(
                'label'         => 'role.field.application',/*$this->translator->trans('role.field.copyRole.label') . ' ' . $data->getName(),*/
                'multiple'      => true,
                'expanded'      => true,
                'class'         => 'CanalTPSamCoreBundle:Application',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.name');
                },
                'translation_domain' => 'messages',
                'property' => 'name'
            ));

            $form->add(
                'rolesAndPerimetersByApplication',
                'collection',
                array(
                    'label' => 'role.field.parent.label',
                    'type' => 'sam_role_by_application',
                    'allow_add'    => false,
                    'allow_delete' => false,
                    'by_reference' => false,
                    'options'      => array(
                        'required'       => true,
                        'error_bubbling' => false,
                        'attr'           => array('class' => 'application-role-box')
                    ),
                )
            );
        }
    }

    /**
     * Fonction appelée lors de l'evenement FormEvents::POST_SET_DATA
     *
     * @param \Symfony\Component\Form\Event\FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        if ($event->getData() instanceof UserRegistration) {
            $this->addPasswordField($event);
        }
    }

    /**
     * Ajoute une valeur dans le champs password pour permettre
     * l'enregistrement en attendant qu'il soit redefini lors
     * de l'activation du compte
     *
     * @param \Symfony\Component\Form\Event\DataEvent $event
     */
    private function addPasswordField(FormEvent $event)
    {
        $data = $event->getData();

        // During form creation setData() is called with null as an argument
        // by the FormBuilder constructor. You're only concerned with when
        // setData is called with an actual Entity object in it (whether new
        // or fetched with Doctrine). This if statement lets you skip right
        // over the null condition.
        if (null === $data->user) {
            return;
        }

        $data->user->setPlainPassword(md5(time()));
        $event->setData($data);
    }

}
