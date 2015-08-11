<?php

namespace CanalTP\SamEcoreUserManagerBundle\Features\Context;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext
{
    /**
     * @AfterScenario
     * @param AfterScenarioScope $scope
     */
    public function afterScenario(AfterScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $doctrineDbalContext = $environment->getContext('CanalTP\SamCoreBundle\Features\Context\DoctrineDbalContext');

        $doctrineDbalContext->truncateTables([
            'public.t_user_usr',
        ]);
    }

    /**
     * @Then /^I get an error saying "(?P<message>[^"]+)" on the field "(?P<field>[^"]+)"$/
     */
    public function iSeeError($message, $field)
    {
        $this->assertElementContainsText('.has-error label', $field);
        $this->assertElementContainsText('.has-error ul.help-block', $message);
    }
}
