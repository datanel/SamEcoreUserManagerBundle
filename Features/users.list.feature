Feature: Users list
    Background:
        Given the following data in "public.t_user_usr" exist:
        | usr_first_name | usr_last_name | usr_username | usr_username_canonical | usr_email           | usr_email_canonical | usr_salt                        | usr_password                                                                             | usr_enabled | usr_locked | usr_last_login | usr_expired | usr_confirmation_token                      | usr_password_requested_at | usr_credentials_expired | cus_id | usr_status | usr_timezone |
        | TestUser       | TestUser      | testuser     | testuser               | testuser@canaltp.fr | testuser@canaltp.fr | 4xxvm3kyqq040scwokw0woggk40k4og | 61QutBUxGwyqRv7Alodpz8aDuflM3T9tU2gTNIcN76M9c1h562OvqUpnAy5otvfdr9yO21fHhobEKAtd9BKGjQ== | TRUE        | FALSE      | 2017-12-07 10:39:15 | FALSE       | MeRhd4K1QZyh_TuuP8M6ft4xRU-p1A6KKZn0zUfPVSk | 2099-09-23 13:36:17       | 0                       | 1      |  3         | Europe/Paris |
        And I am on "/admin/login"
        When I fill in "Nom d'utilisateur :" with "admin@canaltp.fr"
        And I fill in "Mot de passe :" with "admin"
        And I press "Connexion"
        And I am on "/admin/user/list"

    Scenario: Validation Last Connection column
        Then I should see "Liste des utilisateurs"
        And I should see "Dernière connexion"
        And I should see "07/12/2017"
