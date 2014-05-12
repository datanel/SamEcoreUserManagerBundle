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
    public function __construct($class, $aFormUserConfig)
    {
        $this->class = $class;
        $this->aFormUserConfig = $aFormUserConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Ajoute les champs au formulaire de mise à jour des données d'un utilisateur
        foreach ($this->aFormUserConfig as $sFieldName => $aFieldDefinition) {
            $builder->add($sFieldName, $aFieldDefinition['type'], $aFieldDefinition['options']);
        }
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
