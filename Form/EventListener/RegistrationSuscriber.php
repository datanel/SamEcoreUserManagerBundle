<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\EventListener;

use CanalTP\SamCoreBundle\Entity\UserApplicationRole;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use CanalTP\SamEcoreUserManagerBundle\Form\DataTransformer\RoleToRolesTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\SecurityContext;

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
            //FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::SUBMIT => 'submit',
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
        var_dump($data);die;
        // if ($data instanceof User) {
        //     $this->AddApplicationForm($data, $form);
        // }
        // $event->setData($data);

        // $data = $event->getData();
        // $form = $event->getForm();

        $applications = $this->em->getRepository('CanalTPSamCoreBundle:Application')->findAllOrderedByName();
        //$data->rolesAndPerimetersByApplication = $applications;

        $form->add(
            'applications',
            'choice',
            array(
                'label'       => 'role.field.application',
                'multiple'    => true,
                'expanded'    => true,
                'required'    => false,
                'choice_list' => new ObjectChoiceList($applications, 'name')
            )
        );

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

        // $form->add(
        //     'rolesByApplication',
        //     'collection',
        //     array(
        //         'label'        => 'role.field.parent.label',
        //         'type'         => 'sam_copy_role_by_application',
        //         'allow_add'    => false,
        //         'allow_delete' => false,
        //         'by_reference' => false,
        //         'options'      => array(
        //             'required'       => true,
        //             'error_bubbling' => false,
        //             'attr'           => array('class' => 'application-role-box')
        //         ),
        //     )
        // );
    }


    /**
     * Ajoute le formulaire de sélection des applications
     *
     * @param  type $data
     * @param  type $form
     * @return type
     */
    // protected function AddApplicationForm(&$data, &$form)
    // {

    //     // Récupération de l'objet Mode d'id $oCommercialMode->id
    //     $applications = $this->em->getRepository('CanalTPSamCoreBundle:Application')->findAll();

    //     foreach ($applications as $application) {
    //         $applicationRole = new UserApplicationRole();
    //         $applicationRole->setApplication($application);
    //         //$applicationRole->setCurrentRole(null);
    //         $data->addRoleGroupByApplication($applicationRole);
    //     }


    // }

    /**
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        $data = $event->getData();

        $selectedApplications = $data->getGroups();

        $aUserRoles = array();
        $roleGroupByApplications = $data->getRoleGroupByApplications();

        foreach ($roleGroupByApplications as $roleGroupByApplication) {
            $aUserRoles[$roleGroupByApplication->getApplication()->getId()] = $roleGroupByApplication->getParents();
        }

        foreach ($selectedApplications as $selectedApplication) {
            foreach($aUserRoles[$selectedApplication->getId()] as $applicationRole) {
                $data->addApplicationRole($applicationRole);
            }
        }

        $event->setData($data);
    }

    /**
     * Fonction appelée lors de l'evenement FormEvents::POST_SET_DATA
     *
     * @param \Symfony\Component\Form\Event\FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        $this->addPasswordField($event);
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
        if (null === $data) {
            return;
        }

        $data->setPlainPassword(md5(time()));
        $event->setData($data);
    }

}
