Feature: User confirm password at registration
    Background:
        Given the following data in "public.t_user_usr" exist:
            | usr_first_name | usr_last_name | usr_username | usr_username_canonical | usr_email           | usr_email_canonical | usr_salt                        | usr_password                                                                             | usr_enabled | usr_locked | usr_expired | usr_confirmation_token                      | usr_credentials_expired | cus_id | usr_status | usr_timezone |
            | TestUser       | TestUser      | TestUser     | testuser               | testuser@canaltp.fr | testuser@canaltp.fr | 4xxvm3kyqq040scwokw0woggk40k4og | 61QutBUxGwyqRv7Alodpz8aDuflM3T9tU2gTNIcN76M9c1h562OvqUpnAy5otvfdr9yO21fHhobEKAtd9BKGjQ== | TRUE        | FALSE      | FALSE       | MeRhd4K1QZyh_TuuP8M6ft4xRU-p1A6KKZn0zUfPVSk | 0                       | 1      |  1         | Europe/Paris |
        And I am on "/admin/register/confirm/MeRhd4K1QZyh_TuuP8M6ft4xRU-p1A6KKZn0zUfPVSk"

    Scenario: Validation rules presence
        Then I should see an "#validation-rules" element

    Scenario: User creation with same valid passwords
        When I fill in "Nouveau mot de passe :" with "GoodPassword123!"
        And I fill in "Vérification :" with "GoodPassword123!"
        And I press "Enregistrer"
        Then I should see "Félicitations TestUser, votre compte est maintenant activé." in the "#security-login-form .panel-heading" element

    Scenario: User creation with different passwords
        When I fill in "Nouveau mot de passe :" with "GoodPassword123!"
        And I fill in "Vérification :" with "GoodPassword123!differents"
        And I press "Enregistrer"
        Then I get an error saying "Les deux mots de passe ne sont pas identiques" on the field "Nouveau mot de passe :"

    Scenario Outline: User creation password validation failed
        When I fill in "Nouveau mot de passe :" with "<password>"
        And I fill in "Vérification :" with "<password>"
        And I press "Enregistrer"
        Then I get an error saying "Ce mot de passe ne respecte pas les critères de sécurité." on the field "Nouveau mot de passe :"
    Examples:
        |password|
        |passwordest|
        |password123est|
        |password123!?est|
        |password123ARCest|
        |t9A;|
        |Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum|
