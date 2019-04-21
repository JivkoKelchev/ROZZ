<?php

namespace RozzBundle\Controller;

use RozzBundle\Entity\ApplicationSettings;
use RozzBundle\Entity\User;
use RozzBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        
        if($this->getDoctrine()->getRepository(User::class)->findOneBy(['roles'=>'ROLE_ADMIN'])== null)
        {
            $admin = new User();
            $admin->setPassword($this->get('security.password_encoder')->encodePassword($admin,'admin'));
            $admin->setUsername('admin');
            $admin->setName('admin');
            $admin->setPosition('admin');
            $admin->setRoles('ROLE_ADMIN');
            $em = $this->getDoctrine()->getManager();
            $em->persist($admin);
            $em->flush();
        }
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $appSettings = $this->getDoctrine()->getRepository(ApplicationSettings::class)->find(1);
        if ($appSettings){
            $this->get('session')->set('agroYear',$appSettings->getAgroYear());
        }

        return $this->render('@Rozz/Security/login.twig', array('last_username' => $lastUsername,'error' => $error));
    }

    /**
     * @Route("/logout", name="logout")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logoutAction()
    {
        return $this->redirectToRoute('login');
    }
    //todo : при затваряне на сесия да изчистя newContract за юзъра
}
