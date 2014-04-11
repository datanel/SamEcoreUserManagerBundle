<?php

namespace CanalTP\SamEcoreUserManagerBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use FOS\UserBundle\Model\UserInterface;

/**
 * {@inheritdoc}
 */
class RegistrationController extends BaseRegistrationController
{
    public function registerAction()
    {
        $form = $this->container->get('fos_user.registration.form');
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = true;

        $process = $formHandler->process($confirmationEnabled);
        if ($process) {
            $user = $form->getData();

            $this->container->get('session')->set(
                'fos_user_send_confirmation_email/email',
                $user->getEmail()
            );
            $route = 'sam_user_list';

            $this->setFlash('fos_user_success', 'registration.flash.user_created');
            $url = $this->container->get('router')->generate($route);
            $response = new RedirectResponse($url);

            return $response;
        }

        return $this->container->get('templating')->renderResponse(
            'FOSUserBundle:Registration:register.html.'.$this->getEngine(),
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Tell the user to check his email provider
     */
    public function checkEmailAction()
    {
        $email = $this->container->get('session')->get('fos_user_send_confirmation_email/email');
        $this->container->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->container->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(
                sprintf('The user with email "%s" does not exist', $email)
            );
        }

        return $this->container->get('templating')->renderResponse(
            'FOSUserBundle:Registration:checkEmail.html.'.$this->getEngine(),
            array(
                'user' => $user,
            )
        );
    }

    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction($token)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(
                sprintf('The user with confirmation token "%s" does not exist', $token)
            );
        }

        $form = $this->container->get('sam_user.confirmation.form');
        $formHandler = $this->container->get('sam_user.confirmation.form.handler.default');
        $process = $formHandler->process($user);

        if ($process) {
            $user->setEnabled(true);
            $user->setConfirmationToken(null);
            $user->setLastLogin(new \DateTime());

            $response = new RedirectResponse(
                $this->container->get('router')->generate('fos_user_registration_confirmed')
            );
            $this->container->get('fos_user.user_manager')->updateUser($user);
            $this->authenticateUser($user, $response);

            return $response;
        }

        return $this->container->get('templating')->renderResponse(
            'CanalTPSamEcoreUserManagerBundle:Registration:confirm.html.'.$this->getEngine(),
            array(
                'token' => $token,
                'form' => $form->createView(),
            )
        );

    }

}
