<?php

namespace RozzBundle\Controller;

use RozzBundle\Entity\User;

use RozzBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

class UsersController extends Controller
{
    /**
     * @Route("/register", name="register")
     * @Method("GET")
     */
    public function registerAction()
    {
        $form = $this->createForm(UserType::class);
        return $this->render('@Rozz/Security/registration.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/register", name="register_process")
     * @Method("POST")
     */
    public function registerProcessAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isValid()){
            $plainPassword = $user->getPassword();
            $hashedPassword = $this->get('security.password_encoder')->encodePassword($user,$plainPassword);
            $user->setPassword($hashedPassword);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('home');
        }else{
            return $this->redirectToRoute('register_process');
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/profile", name="userProfile")
     */
    public function ChangePaswordAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()){
            $plainPassword = $user->getPassword();
            $hashedPassword = $this->get('security.password_encoder')->encodePassword($user,$plainPassword);
            $user->setPassword($hashedPassword);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('home');
        }else{
            return $this->render('@Rozz/Security/user_profile.html.twig', ['form'=>$form->createView()]);
        }
    }
//    /**
//     * @Route("/user/profile", name="user_profile")
//     *
//     * @Security("is_fully_authenticated()")
//     */
//    public function userProfileAction(Request $request)
//    {
//        $gs = $this->getDoctrine()->getRepository(General::class)->find(1);
//        $user = $this->getUser();
//        $userPassHeshed = $user->getPassword();
//        $form = $this->createForm(UserProfileType::class, $user);
//        $form->handleRequest($request);
//        if($form->isSubmitted() && $form->isValid()){
//            if ($user->getPassword()){
//                $plainPassword = $user->getPassword();
//                $hashedPassword = $this->get('security.password_encoder')->encodePassword($user,$plainPassword);
//                $user->setPassword($hashedPassword);
//            }else{
//                $user->setPassword($userPassHeshed);
//            }
//            $uploadDir = $this->getParameter('upload_directory');
//            $em = $this->getDoctrine()->getManager();
//            $this->get('form_helper_service')->userProfileSetter($user,$em,$uploadDir);
//            return $this->redirectToRoute('user_profile');
//        }
//        return $this->render('@CMS/User/user_profile.html.twig',['form'=>$form->createView(), 'gs'=>$gs]);
//    }

}
