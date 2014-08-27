<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Role\Role;
use CanalTP\SamEcoreUserManagerBundle\Form\Model\UserRegistration;

class RegistrationSuscriber implements EventSubscriberInterface
{
    private $customers = null;
    protected $em;
    protected $context;

    public function __construct(EntityManager $em, SecurityContext $context)
    {
        $this->em = $em;
        $this->context = $context;
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

    private function initCustomerField(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        $repository = $this->em->getRepository('CanalTPSamCoreBundle:Customer');
        $isSuperAdmin = $this->context->getToken()->getUser()->hasRole('ROLE_SUPER_ADMIN');
        $data->customer = $data->user->getCustomer();
        if ($isSuperAdmin) {
            $choices = $repository->findAllToArray();
        } else {
            $choices = $repository->findByToArray(array(
                'id' => $data->user->getCustomer()
            ));
        }

        $form->add('customer', 'choice', array(
            'label' => 'role.field.customer',
            'expanded' => false,
            'choices' => $choices,
            'empty_value' => ($isSuperAdmin ? 'global.please_choose' : false),
            'disabled' => ($isSuperAdmin ? false : true),
            'translation_domain' => 'messages'
        ));
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

            $appsRolesPerims = array();
            foreach ($applications as $application) {
                $appRolePerim = new \CanalTP\SamEcoreApplicationManagerBundle\Form\Model\ApplicationRolesPerimeters();
                $appRolePerim->application = $application;
                $appRolePerim->superAdmin = false;
                $appsRolesPerims[] = $appRolePerim;
            }

            $data->rolesAndPerimetersByApplication = $appsRolesPerims;

            $this->initCustomerField($event);
        }
    }

    /**
     * Fonction appelée lors de l'evenement FormEvents::POST_SET_DATA
     *
     * @param \Symfony\Component\Form\Event\FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        if ($event->getData() instanceof UserRegistration && is_null($event->getData()->user->getId())) {
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
