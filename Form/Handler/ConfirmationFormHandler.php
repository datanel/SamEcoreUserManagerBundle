<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CanalTP\SamEcoreUserManagerBundle\Form\Handler;

use FOS\UserBundle\Model\User;
use FOS\UserBundle\Model\UserManagerInterface;
use CanalTP\SamEcoreUserManagerBundle\Form\Model\ConfirmUser;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ConfirmationFormHandler
{
    protected $request;
    protected $userManager;
    protected $form;

    public function __construct(
        FormInterface $form,
        Request $request,
        UserManagerInterface $userManager
    ) {
        $this->form = $form;
        $this->request = $request;
        $this->userManager = $userManager;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->form->getData()->firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->form->getData()->lastname;
    }

    /**
     * @return string
     */
    public function getNewPassword()
    {
        return $this->form->getData()->new;
    }

    public function process(User $user)
    {
        $this->form->setData(new ConfirmUser());

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }

    protected function onSuccess(User $user)
    {
        $user->setFirstname($this->getFirstname());
        $user->setLastname($this->getLastname());
        $user->setPlainPassword($this->getNewPassword());
        $user->setConfirmationToken(null);
        $user->setPasswordRequestedAt(null);
        $user->setEnabled(true);
        $this->userManager->updateUser($user);
    }
}
