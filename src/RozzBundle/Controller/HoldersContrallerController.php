<?php

namespace RozzBundle\Controller;

use RozzBundle\Entity\Holders;
use RozzBundle\Form\HolderType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class HoldersContrallerController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/holders/list", name="holders_list")
     */
    public function listHoldersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $holders = $em->getRepository("RozzBundle:Holders")->findAll();
        return $this->render('@Rozz/Holders/list_holders.html.twig',['holders'=>$holders]);
    }


    /**
     * @param $id
     * @Route("/holder/for/contract/{id}", name="add_holder_for_contract")
     */
    public function addHolderForContractAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $holder = $em->getRepository("RozzBundle:Holders")->find($id);
        dump($holder);
        exit;
    }

    /**
     * @Route("/holder/edit", name="edit_holder")
     *
     */
    public function editHolderAction( Request $request){

        $id = $request->query->get('id');
        $em = $this->getDoctrine()->getManager();
        $holder = $em->getRepository('RozzBundle:Holders')->find($id);
        $form = $this->createForm(HolderType::class, $holder);
        $form->handleRequest($request);
        if ($form->isValid() & $form->isSubmitted()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($holder);
            $em->flush();
            return $this->redirectToRoute('holders_list');
        }
        return $this->render('@Rozz/Holders/create_holders.html.twig',['form'=>$form->createView()]);
    }

    /**
     * @Route("/holder/create", name="create_holder")
     */
    public function createHolder(Request $request)
    {
        $holder = new Holders();
        $form = $this->createForm(HolderType::class, $holder);
        $form->handleRequest($request);
        if ($form->isValid() & $form->isSubmitted()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($holder);
            $em->flush();
            return $this->redirectToRoute('holders_list');
        }
        return $this->render('@Rozz/Holders/create_holders.html.twig',['form'=>$form->createView()]);
    }

    /**
     * @Route("/valid-egn-bulstat", name="valid-egn-bulstat")
     */
    public function IsEgnOrBulstatValidAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $result = $this->get('egn_bulstat_sevrice')->isValidEgnBulstat($request->get('input'));
            $output = ($result) ? 'true' : 'false';
            return new JsonResponse($output);
        }
    }
}
