<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CanalTP\SamEcoreUserManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfirmationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'new',
                'repeated',
                array(
                    'type' => 'password',
                    'options' => array('translation_domain' => 'FOSUserBundle'),
                    'first_options' => array('label' => 'form.new_password'),
                    'second_options' => array('label' => 'form.new_password_confirmation'),
                    'invalid_message' => 'fos_user.password.mismatch',
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\SamEcoreUserManagerBundle\Form\Model\ConfirmUser',
                'intention'  => 'confirmation',
            )
        );
    }

    public function getName()
    {
        return 'canaltp_user_confirmation';
    }
}
