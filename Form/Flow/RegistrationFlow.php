<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Flow;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use CanalTP\SamEcoreUserManagerBundle\Form\Type\RegistrationStepOneFormType;

class RegistrationFlow extends FormFlow
{
    private $userType;
    private $userAssignCustomerType;

    /**
     * @inherit
     */
    public function __construct($userType, $userAssignCustomerType)
    {
        $this->userType = $userType;
        $this->userAssignCustomerType = $userAssignCustomerType;
        // $this->setAllowDynamicStepNavigation(true);
    }

    public function getName()
    {
        return 'registration';
    }

    public function setCurrentStepNumber($nb)
    {
        $this->currentStepNumber = $nb;
    }

    protected function loadStepsConfig()
    {
        return array(
            array(
                'label' => 'form.user.step_1.title',
                'type' => $this->userType
            ),
            array(
                'label' => 'form.user.step_2.title',
                'type' => $this->userAssignCustomerType
            )
        );
    }

}
