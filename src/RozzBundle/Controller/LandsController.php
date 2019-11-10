<?php

namespace RozzBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use RozzBundle\Entity\ApplicationSettings;
use RozzBundle\Entity\Comments;
use RozzBundle\Entity\Contracts;
use RozzBundle\Entity\Lands;
use RozzBundle\Entity\Mest;
use RozzBundle\Entity\NewContracts;
use RozzBundle\Entity\SelectedLand;
use RozzBundle\Entity\UsedArea;
use RozzBundle\Entity\User;
use RozzBundle\Entity\Zem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class LandsController
 * @package RozzBundle\Controller
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class LandsController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction(Request $request)
    {
        return $this->redirectToRoute('view_filtred_lands',['num'=>'all','mest'=>'all','zem'=>'all']);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/lands/{num}/{mest}/{zem}/", name="view_filtred_lands")
     *
     */
    public function viewFiltredLandsAction(Request $request, $num = 'all', $mest = 'all', $zem = 'all')
    {   //set filterdata to render the form
        if($num == 'all'){$num = null;}
        $filterData['num']=$num;
        if($mest == 'all'){$mest = null;}
        $filterData['mest']=$mest;
        if($zem == 'all'){$zem = null;}
        $filterData['zem']=$zem;

        $em = $this->getDoctrine()->getManager();
//Create Name Array for ChoiceType in Filter Form!!
        $mests = $em->getRepository(Mest::class)->findAll();
        $mestNamesArray = [];
        $zems = $em->getRepository(Zem::class)->findAll();
        $zemsNamesArray = [];
        foreach ($mests as $mestEntity){
            $mestNamesArray[$mestEntity->getName()]=$mestEntity->getName();
        }
        foreach ($zems as $zemEntity){
            $zemsNamesArray[$zemEntity->getName()]=$zemEntity->getName();
        }
//Create Filter Form with $filterData[]
        $filterForm = $this->createFormBuilder($filterData)
            ->getForm()->add('num', TextType::class,['label'=>'№ имот','required'=>false])
            ->add('mest',ChoiceType::class,['label'=>'Местност', 'required'=>false, 'choices'=>$mestNamesArray])
            ->add('zem', ChoiceType::class,['label'=>'Землище', 'required'=>false, 'choices'=>$zemsNamesArray]);


        $lands = $this->get('form_handler_service')->landFilter($filterData, $em);
//Create Pagination
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $lands, /*or query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
                10/*limit per page*/,
                ['wrap-queries'=>true]);
//Handle filter form
        $filterForm->handleRequest($request);
        if ($filterForm->isValid() && $filterForm->isSubmitted()){
            $filterData = $filterForm->getData();
            if($filterData['num'] == null){$filterData['num']= 'all';};
            if($filterData['mest'] == null){$filterData['mest'] = 'all';}
            if($filterData['zem'] == null){$filterData['zem'] = 'all';}
            return $this->redirectToRoute('view_filtred_lands',['num'=>$filterData['num'],'mest'=>$filterData['mest'],'zem'=>$filterData['zem']]);
        }
        $selected = $em->getRepository(SelectedLand::class)->findBy(['user'=>$this->getUser()]);
        $selectedLands = [];
        foreach ($selected as $selectedLand){
            $selectedLands[] = $selectedLand->getLand();
        }

        //set null values to 'all'
        foreach ($filterData as $key => $data){
            $filterData[$key] = $filterData[$key] === null ? 'all' : $filterData[$key];
        }
        return $this->render(
            "@Rozz/Lands/view_lands.html.twig",
                [
                    'lands'=>$lands,
                    'selectedLands'=>$selectedLands,
                    'pagination' => $pagination,
                    'filterForm'=>$filterForm->createView(),
                    'filterData' => $filterData
                ]
            );
    }

    /**
     * @param $landId
     * @Route( "/lands/renderActiveContracts/{landId}" , name="renderActiveContracts" )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderActiveContractsForLandAction($landId)
    {
        $em = $this->getDoctrine()->getManager();
        $land = $em->getRepository(Lands::class)->find($landId);
        $usedArea = $em->getRepository(UsedArea::class)->findBy(['land'=>$land]);
        $activeContracts = [];
        foreach ($usedArea as $contractArea){
            if ( $contractArea->getActive() == 1){
                $activeContracts[] = $contractArea->getContract();
            }
        }
        return $this->render('@Rozz/Lands/lands_getActiveContracts_partial.html.twig',['contracts'=>$activeContracts]);
    }

    /**
     * @param $landId
     * @Route( "/lands/renderFreeAreaForLandAction/{landId}" , name="renderFreeAreaForLandAction" )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderFreeAreaForLandAction($landId)
    {
        $em = $this->getDoctrine()->getManager();
        $land = $em->getRepository(Lands::class)->find($landId);
        $landArea = $land->getArea();

        $freeArea = $this->get('lands_service')->getLandFreeArea($em,$landId);

        return $this->render('@Rozz/Lands/lands_renderFreeArea_partial.html.twig',['area'=>$landArea, 'freeArea' => $freeArea]);
    }


    /**
     * @param Request $request
     * @Route("/land/add/selected/{id}", name="add_to_selected")
     */
    public function addToSelectedAction(Request $request, $id)
    {
//add to selected in DB
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $land = $em->getRepository(Lands::class)->find($id);
        if($em->getRepository(SelectedLand::class)->findOneBy(['land'=>$land,'user'=>$this->getUser()])){
            $flashMassage = "Имота вече е избран!";
            $this->get('session')->getFlashBag()->add('error', $flashMassage);
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }else{
            $selected = new SelectedLand();
            $usedArea = 0;
            $usedAreaArray = $em->getRepository('RozzBundle:UsedArea')->findBy(['land'=>$land]);
            foreach ($usedAreaArray as $area){
                $expire = $area->getContract()->getExpire();
                $now = new \DateTime('now');
                if($now<$expire){
                    $usedArea = $usedArea + $area->getArea();
                }
            }
            $maxArea = $land->getArea() - $usedArea;
            $selected->setArea($maxArea);
            $selected->setLand($land);
            $selected->setUser($user);
            $em->persist($selected);
            $em->flush();
            $newContract = $em->getRepository(NewContracts::class)->findOneBy(['user'=>$this->getUser()]);
            if (!$newContract){
                $newContract = new NewContracts();
                $newContract -> setUser($this->getUser());
            }
            $neighboursArray = $newContract->getNeighbours();
            $neighboursArray[$land->getNum()] = ' ';
            $newContract->setNeighbours($neighboursArray);
            $em->persist($newContract);
            $em->flush();
            $flashMassage = "Имот № ".$land->getNum()." е добавен в избрани имоти. Можете да направите нов договор за този имот.";
            $this->get('session')->getFlashBag()->add('success', $flashMassage);
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @Route("/lands/comment/{id}", name="lands_add_comment")
     */
    public function addComentAction($id, Request $request){
        $em = $this->getDoctrine()->getManager();
        $land = $em->getRepository('RozzBundle:Lands')->find($id);
        $formData = ['comment'=>null];
        if($land->getComments()){
            $formData['comment']=$land->getComments()->getComment();
        }
        $form = $this->createFormBuilder($formData)->getForm()
            ->add('comment',TextareaType::class, ['label'=>'Забележка :','attr' =>['rows'=>"15", 'cols'=>"70"]]);

        $form->handleRequest($request);
        if ($form->isSubmitted()&&$form->isValid()){
            $formData = $form->getData();
            if($land->getComments()){
                $comment = $land->getComments();
            } else {
                $comment = new Comments();
            }
            $comment->setLand($land);
            $comment->setComment($formData['comment']);
            $land->setComments($comment);
            $em->persist($land);
            $em->flush();
            $msg = "Добавена е забележка към имот №". $land->getNum(). ", м.". $land->getMest()->getName().', зем.'.$land->getZem()->getName();
            $this->get('session')->getFlashBag()->add('success',$msg);
            return $this->redirectToRoute('view_filtred_lands',['num'=>$land->getNum(),$land->getMest()->getName(),'zem'=>$land->getZem()->getName()]);
        }
        return $this->render('@Rozz/Lands/lands_add_comment.html.twig',['form'=>$form->createView(),'comment'=>$land->getComments()]);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/lands/comments-delete/{id}")
     */
    public function deleteCommentAction($id){
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('RozzBundle:Comments')->find($id);
        $land = $comment->getLand();
        $em->remove($comment);
        $em->flush();
        return $this->redirectToRoute('view_filtred_lands',['num'=>$land->getNum(),$land->getMest()->getName(),'zem'=>$land->getZem()->getName()]);
    }

    /**
     * @Route("/lands/export/{num}/{mest}/{zem}", name="lands_export")
     */
    public function exportLands($num, $mest, $zem)
    {
        if($num === $mest && $mest === $zem && $zem === 'all')
        {
            $this->get('session')->getFlashBag()->set('error', 'Твърде голяма заявка, използвай филтъра за да ограничиш резултатите.');
            return $this->redirectToRoute('view_filtred_lands',['num'=>$num,'mest'=>$mest,'zem'=>$zem]);
        }
        $filterData['num'] = $num === 'all' ? null : $num;
        $filterData['mest'] = $mest === 'all' ? null : $mest;
        $filterData['zem'] = $zem === 'all' ? null : $zem;
        $em = $this->getDoctrine()->getManager();

        $landsQuery = $this->get('form_handler_service')->landFilter($filterData, $em, false);
        $lands = $landsQuery->getResult();

        $fileName = $this->get('excel_service')->landsExport($lands);
        return $this->file($fileName);
    }

}
