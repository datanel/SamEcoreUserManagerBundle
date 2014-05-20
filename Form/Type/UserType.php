<?php

/**
 * Description of ProfileFormType
 *
 * @author akambi
 */

namespace CanalTP\SamEcoreUserManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    protected $class;
    protected $aFormUserConfig;

    /**
     * @param string $class The User class name
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'username',
            'text',
            array(
                'label' => 'form.username',
                'attr' => array(
                    'class' => 'col-md-4',
                    'placeholder' => 'enter username'
                ),
                'translation_domain' => 'FOSUserBundle'
            )
        );

        $builder->add(
            'firstname',
            'text',
            array(
                'label' => 'form.firstname',
                'attr' => array(
                    'class' => 'col-md-4',
                    'placeholder' => 'enter firstname'
                ),
                'translation_domain' => 'FOSUserBundle'
            )
        );

        $builder->add(
            'lastname',
            'text',
            array(
                'label' => 'form.lastname',
                'attr' => array(
                    'class' => 'col-md-4',
                    'placeholder' => 'enter lastname'
                ),
                'translation_domain' => 'FOSUserBundle'
            )
        );

        $builder->add(
            'email',
            'text',
            array(
                'label' => 'form.email',
                'attr' => array(
                    'class' => 'col-md-4',
                    'placeholder' => 'enter email'
                ),
                'translation_domain' => 'FOSUserBundle'
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->class,
                'intention'  => 'sam_user',
            )
        );
    }

    public function getName()
    {
        return 'sam_user';
    }
}
