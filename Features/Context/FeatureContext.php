<?php

namespace CanalTP\SamEcoreUserManagerBundle\Features\Context;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

class FeatureContext extends MinkContext implements SnippetAcceptingContext
{
    use KernelDictionary;
    /**
     * @AfterScenario
     * @param AfterScenarioScope $scope
     */
    public function afterScenario(AfterScenarioScope $scope)
    {
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');
        $connection->executeUpdate("DELETE FROM public.t_user_usr WHERE usr_email!='admin@canaltp.fr'");
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
