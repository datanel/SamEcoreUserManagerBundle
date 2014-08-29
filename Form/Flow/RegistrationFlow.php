<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Flow;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use CanalTP\SamEcoreUserManagerBundle\Form\Type\RegistrationStepOneFormType;

class RegistrationFlow extends FormFlow
{
    protected $userFormType;

    /**
     * @inherit
     */
    public function __construct($userFormType)
    {
        $this->userFormType = $userFormType;
    }

    public function getName()
    {
        return 'registration';
    }

    protected function loadStepsConfig()
    {
        return array(
            array(
                'label' => 'User info',
                'type' => $this->userFormType
            )
        );
    }

}
