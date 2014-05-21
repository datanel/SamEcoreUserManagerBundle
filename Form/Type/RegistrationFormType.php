<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationFormType extends BaseRegistrationFormType
{
    protected $registrationListener;

    /**
     * @inherit
     */
    public function __construct($registrationListener)
    {
        $this->registrationListener = $registrationListener;
    }

    /**
     * @inherit
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', 'sam_user');
        $builder->addEventSubscriber($this->registrationListener);
    }

    /**
     * @inherit
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\SamEcoreUserManagerBundle\Form\Model\UserRegistration',
                'csrf_protection' => false,
            )
        );
    }

    public function getName()
    {
        return 'sam_user_registration';
    }
}
