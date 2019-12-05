<?php

namespace RozzBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use RozzBundle\Entity\ApplicationSettings;
use RozzBundle\Entity\Contracts;
use RozzBundle\Entity\Examiners;
use RozzBundle\Entity\Holders;
use RozzBundle\Entity\Mayors;
use RozzBundle\Entity\NewContracts;
use RozzBundle\Entity\SelectedLand;
use RozzBundle\Entity\UsedArea;
use RozzBundle\Form\NewContractType;
use RozzBundle\Form\SelectExaminer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ContractsController extends Controller
{
    /**
     * @Route("/newContract/type", name="new_contract_type")
     *
     */
    public function selectNewContractTypeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $newContractEntity = $em->getRepository(NewContracts::class)->findOneBy(['user'=>$this->getUser()]);
        if (!$newContractEntity){
            $newContractEntity = new NewContracts();
            $newContractEntity->setUser($this->getUser());
            $em->persist($newContractEntity);
            $em->flush();
        }
        if($newContractEntity->getType()){
            $formSelectedChoice = strval($newContractEntity->getType());
        }else{
            $formSelectedChoice = '1';
        }
        $formData = [];
        $form = $this->createFormBuilder($formData)->add('type',ChoiceType::class,['label'=>'Избери вида на договора :',
        'choices'=>['Нов Договор ( празен )'=>'1','Нов Договор ( от съществуващ )'=>'2', 'Анекс към договор'=>'3'],
        'data'=>$formSelectedChoice])
           ->getForm();
        $form->handleRequest($request);
        if($form->isValid() && $form->isSubmitted()){
            $formData = $form->getData();

            $newContractEntity->setType($formData['type']);
            $em->persist($newContractEntity);
            $em->flush();
            if ( ($newContractEntity->getType() == 3) or ($newContractEntity-> getType() == 2) ){
                return $this->redirectToRoute('contracts_list');
            }
            return $this->redirectToRoute('selected_lands');
        }
        return $this->render('@Rozz/Contracts/contract_select_type.html.twig',['form'=>$form->createView()]);
    }


    /**
     * @Route("/ContractFromExisting/{contractId}", name = "ContractFromExisting")
     */
    public function newContractFromExistingContractAction($contractId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $selectedContract = $em->getRepository(Contracts::class)->find($contractId);
        if (!$selectedContract->getNum()){
            $massage = 'Избрания договор няма въведен номер!';
            $this->get('session')->getFlashBag()->add('error', $massage);
            return $this->redirectToRoute('contracts_list');
        }
        $this->get('contract_service')->setNewContractFromExistingContract($em,$user,$contractId);
        return $this->redirectToRoute('selected_lands');
    }

    /**
     * @param Request $request
     * @Route("/selected", name="selected_lands")
     */
    public function manageSelectedAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $newContract = $em->getRepository('RozzBundle:NewContracts')->findOneBy(['user'=>$this->getUser()]);
        if($newContract){
            $contractType = $newContract->getType();
        }else{
            $contractType = null;
        }
        $selectedLands = $em->getRepository(SelectedLand::class)->findBy(['user'=>$this->getUser()]);
        $selectedLandsData = [];
        $selectedLandsFormBuilder = $this->createFormBuilder($selectedLandsData);
        $selectedLandsFormBuilder = $this->get('contract_service')->
                                    createSelectedLandsForm($selectedLandsFormBuilder,$selectedLands, $newContract);

        $selectedLandsForm = $selectedLandsFormBuilder->getForm();
        $selectedLandsForm->handleRequest($request);
        if($selectedLandsForm->isSubmitted() && $selectedLandsForm->isValid())
        {
            $selectedLandsData = $selectedLandsForm->getData();
            foreach ($selectedLands as $index=>$land){
                $land->setArea($selectedLandsData[$index]);
                $land->setPrice($selectedLandsData[$index+100]);
                $em->persist($land);
                $em->flush();
            }
            return $this->redirectToRoute('select_holder');
        }
        return $this->render('@Rozz/Contracts/manage_selected_lands.html.twig',['form'=>$selectedLandsForm->createView(),
                                                                                            'lands' => $selectedLands]);
    }

    /**
     * @Route("selected/remove/{id}", name="remove_selected_land")
     */
    public function removeSelectedLandAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $selectedLand = $em->getRepository('RozzBundle:SelectedLand')->find($id);
        $newContract = $em->getRepository(NewContracts::class)->findOneBy(['user'=>$this->getUser()]);
        $neighboursArray = $newContract->getNeighbours();
        if ($neighboursArray != null){
            unset($neighboursArray[$selectedLand->getLand()->getNum()]);
        }
        $newContract->setNeighbours($neighboursArray);
        $em->persist($newContract);
        $em->remove($selectedLand);
        $em->flush();
        return $this->redirectToRoute('selected_lands');
    }

    /**
     * @Route("/holder/select", name="select_holder")
     */
    public function selectHolderAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $newContract = $em->getRepository(NewContracts::class)->findOneBy(['user'=>$this->getUser()]);
        if (!$newContract){
            $newContract = new NewContracts();
            $newContract->setUser($this->getUser());
            $em->persist($newContract);
            $em->flush();
        }
        $selectedHolder = $newContract->getHolder();
        $holders = $em->getRepository(Holders::class)->findAll();
        $holdersNames = [];
        foreach ($holders as $holder){
            $holdersNames[$holder->getName()]=$holder->getName();
        }
        /**@var Form $holderForm*/
        // масив за формата
        $holderData=[];
        if ($newContract->getHolder()){
            $holderData['names'] = $newContract->getHolder()->getName();
        }
        $holderForm = $this->createFormBuilder($holderData)
            ->add('egn',TextType::class,['label'=>'Въведи ЕГН','required'=>false])
            ->add('names',ChoiceType::class,['label'=>'Избери име', 'choices'=>$holdersNames, 'required'=>false])
            ->getForm();
        $holderForm->handleRequest($request);
        if($holderForm->isSubmitted() && $holderForm->isValid())
        {
            $holderData = $holderForm->getData();
            $holder = $this->get('form_handler_service')->selectHolder($holderData,$em);
            if(is_array($holder)){
                $this->get('session')->getFlashBag()->add('error', $holder['error']);
                return $this->render('@Rozz/Contracts/select_holder.html.twig',['form'=>$holderForm->createView(), 'holder'=>$selectedHolder]);
            }else{
                $newContract->setHolder($holder);
                $em->persist($newContract);
                $em->flush();
                return $this->redirectToRoute('new_contract');
            }
        }
        return $this->render('@Rozz/Contracts/select_holder.html.twig',['form'=>$holderForm->createView(), 'holder'=>$selectedHolder]);
    }



    /**
     * @Route("contract/new", name="new_contract")
     */
    public function newContractAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $newContractEntity = $em->getRepository(NewContracts::class)->findOneBy(['user'=>$this->getUser()]);
        if ($newContractEntity->getNeighbours() == null){
            $neighbours = [];
            $selected = $em->getRepository(SelectedLand::class)->findBy(['user'=>$this->getUser()]);
            foreach ($selected as $land){
                $neighbours[$land->getLand()->getNum()] = '';
            }
            $newContractEntity->setNeighbours($neighbours);
        }
        $form = $this->createForm(NewContractType::class,$newContractEntity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $appSettings = $em->getRepository(ApplicationSettings::class)->find(1);
            if (!$appSettings){
                //TODO Да направя автоматична инициализация на appSettings!
                dump('Няма зададено начало на стопанската година');
                exit;
            }else{
                $dateInterval = new \DateInterval('P'. strval($newContractEntity->getYears()) .'Y');
                $oneDayInterval = new \DateInterval('P1D');
                $newContractStart = new \DateTime($appSettings->getAgroYear()->format('Y-m-d H:i:s')) ;
                $newContractExpire = new \DateTime($appSettings->getAgroYear()->format('Y-m-d H:i:s'));
                $newContractExpire->add($dateInterval);
                $newContractExpire->sub($oneDayInterval);
            }
            $newContractEntity->setStart($newContractStart);
            $newContractEntity->setExpire($newContractExpire);
            $em->persist($newContractEntity);
            $em->flush();
            return $this->redirectToRoute('contract_preview');
        }
        return $this->render('@Rozz/Contracts/new_contract.html.twig', ['form' => $form->createView(),'data'=>$newContractEntity]);
    }

    /**
     * @Route("contract/add-examiner", name="add-examiner-to-contract")
     */
    public function addExaminerToNewContract(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $allExaminers = $em->getRepository(Examiners::class)->findAll();
        $choices = [];
        foreach ($allExaminers as $examiner){
            $choices[$examiner->getName()] = $examiner->getId();
        }

        $formData = [];
        $form = $this->createFormBuilder($formData)
            ->add('name', ChoiceType::class,['label'=>'Съгласувал', 'choices' => $choices])
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $newContract = $em->getRepository(NewContracts::class)->findOneBy(['user' => $this->getUser()]);

            $formData = $form->getData();
            $examiner = $em->getRepository(Examiners::class)->find($formData['name']);
            /**
             * @var ArrayCollection $examiners
             */
            $examiners = $newContract->getExaminers();

            if($examiners->contains($examiner)){
                $this->get('session')->getFlashBag()->add('error', $examiner->getName().' вече е добавен!');
                return $this->render('@Rozz/Contracts/select_examiner.html.twig', ['form' => $form->createView()]);
            }
            $newContract->getExaminers()->add($examiner);
            $em->persist($newContract);
            $em->flush();
            return $this->redirectToRoute('new_contract');
        }

        return $this->render('@Rozz/Contracts/select_examiner.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/contract/examiner/remove/{id}", name="remove-examiner-from-new-contract")
     */
    public function removeExaminerFromNewContract($id)
    {
        $em = $this->getDoctrine()->getManager();

        $newContract = $em->getRepository(NewContracts::class)->findOneBy(['user' => $this->getUser()]);
        /**
         * @var ArrayCollection $examiners
         */
        $examiners = $newContract->getExaminers();
        $examiner = $em->getRepository(Examiners::class)->find($id);

        $examinerIndex = $examiners->indexOf($examiner);
        $examiners->remove($examinerIndex);

        $newContract->setExaminers($examiners);
        $em->persist($newContract);
        $em->flush();

        return $this->redirectToRoute('new_contract');
    }

    /**
     * @Route("contract/preview", name="contract_preview")
     */
    public function previewContractAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        /**
         * @var NewContracts $newContract
         */
        $newContract = $em->getRepository(NewContracts::class)->findOneBy(['user'=>$user]);

        $selectedLands = $em->getRepository(SelectedLand::class)->findBy(['user'=>$user]);

        $newContract->setMayor($em->getRepository(Mayors::class)->findOneBy(['status'=>1]));

        $differenceHtml = '';
        if ($newContract->getType() == 3){
            $differenceHtml = $this->get('contract_service')->getAnnexDifference($newContract,$em,$this->getUser());
        }
        //
        $neighbours = $newContract->getNeighbours(true);
        //set neighbours to selected lands
        foreach ($selectedLands as $land){
            if($neighbours[$land->getLand()->getNum()]){
                $land->setNeighbours($neighbours[$land->getLand()->getNum()]);
            }
        }

        if ($this->get('contract_service')->checkDataForContract($user,$em)){
            return $this->render('@Rozz/Contracts/contracts_preview.html.twig',
                ['data'=>$newContract,
                'lands'=>$selectedLands,
                'difference' => $differenceHtml]);
        }else{
            //ToDo: Error flashbag
            return $this->redirectToRoute('view_filtred_lands');
        }

    }

    /**
     * @Route("contract/view/{id}",name="contract_view")
     */
    public function viewContractAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $contract = $em->getRepository(Contracts::class)->find($id);
        if($contract->getStart() == null){
            $start = clone $contract->getExpire();
            $start->modify('-1 year')->modify('+1 day');
            $contract->setStart($start);
        }
        $differenceHtml = '';
        if ($contract->getType() == 3){
           $differenceHtml = $this->get('contract_service')->getAnnexDifference($contract,$em,$contract->getUser());
        }
        //
        $neighbours = $contract->getNeighbours(true);
        //set neighbours to selected lands
        foreach ($contract->getUsedArea() as $area){
            if($neighbours[$area->getLand()->getNum()]){
                $area->setNeighbours($neighbours[$area->getLand()->getNum()]);
            }
        }
        return $this->render('@Rozz/Contracts/contract_view.html.twig',
            ['data'=>$contract,
            'lands'=>$contract->getUsedArea(),
            'difference' => $differenceHtml]);
    }

    /**
     * @Route("contract/create",name="contract_create")
     */
    public function createContractAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //RTF Template
        $templateDir = $this->get('kernel')->getRootDir()."/../web/files/rtf.rtf";
        if ($this->get('contract_service')->checkDataForContract($user,$em)){

            $contractId = $this->get('contract_service')->persistContract($user,$em, $templateDir);
            return $this->redirectToRoute('contract_view',['id'=>$contractId]);
        }else{
            //TODO : Error flashbag
            return  $this->redirectToRoute('view_filtred_lands');
        }
    }

    /**
     * @param $id
     * @Route("/contract/rtf/{id}",name="contract_rtf")
     */
    public function makeRtfAction($id){
        $em = $this->getDoctrine()->getManager();
        $contract = $em->getRepository(Contracts::class)->find($id);

        $dir = $this->get('kernel')->getRootDir()."/../web/files/";

        $filePath = $this->get("contract_service")->createRtf($contract,$dir);
        return $this->file($filePath);
    }



    /**
     * @Route("/contract/delete/{id}", name="contract_delete")
     */
    public function deleteContractAction($id){
        $em = $this->getDoctrine()->getManager();
        $contract = $em->getRepository('RozzBundle:Contracts')->find($id);
        $usedArea = $contract->getUsedArea();
        foreach ($usedArea as $entity){
            $em->remove($entity);
        }
        $fs = new Filesystem();
        $fs->remove($this->get('kernel')->getRootDir()."/../web/files/".'/rtf_files/'.$contract->getDocFile());
        $em->remove($contract);
        $em->flush();
        return $this->redirectToRoute('view_filtred_lands');
    }

    /**
     * @param Request $request
     * @Route("contracts/list", name="contracts_list")
     */
    public function contractsListAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $searchArray = [];
        $searchArray['search']=null;
        $searchForm = $this->createFormBuilder($searchArray)
            ->getForm()
            ->add('search', TextType::class,['label'=>'Търси договор','required'=>false]);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid())
        {
            $searchArray = $searchForm->getData();
            $result = $this->get('form_handler_service')->searchContract($searchArray,$em);
            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate($result['result'], /*or query NOT result */
                $request->query->getInt('page', 1)/*page number*/,10/*limit per page*/);
            return $this->render("@Rozz/Contracts/contracts_list.html.twig", ['contracts'=>$result['result'], 'pagination' => $pagination, 'searchForm'=>$searchForm->createView()]);
        }else{
            $result = $this->get('form_handler_service')->searchContract($searchArray,$em);
            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate($result['result'], /*or query NOT result */
                $request->query->getInt('page', 1)/*page number*/,10/*limit per page*/);
            return $this->render("@Rozz/Contracts/contracts_list.html.twig", ['contracts'=>$result['result'], 'pagination' => $pagination, 'searchForm'=>$searchForm->createView()]);

        }

    }

    /**
     * @Route("/contract/edit/{contractId}", name="contracts_edit")
     * Param int $contractId
     */
    public function contractsEditAction(int $contractId){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $this->get('contract_service')->setNewContractForEdit($em,$user,$contractId);

        return $this->redirectToRoute('selected_lands');
    }

    /**
     * @Route("/contracts/clearNewContract", name="contracts_clear_new_contract")
     */
    public function clearNewContract()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $this->get("contract_service")->clearNewContractAndSelected($em,$user);

        return $this->redirectToRoute('home');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/contract/statistics", name="contracts_statistics")
     */
    public function contractsStatisticsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $timeArray = [];
        $timeArray['start']=null;
        $timeArray['end']= new \DateTime('now');
        $timeForm = $this->createFormBuilder($timeArray)
            ->getForm()
            ->add('all', CheckboxType::class, ['label'=>'само активни договори', 'required'=>false])
            ->add('start', DateType::class, ['label'=>'Oт : ', 'required'=>true, 'html5'=>false,
                'format' => 'dd.MM.yyyy'])
            ->add('end', DateType::class, ['label'=>'До : ', 'required'=>true,
                'format' => 'dd.MM.yyyy'])
            ->add('criteria', TextType::class,['label'=>'Критерии :','required'=>false]);

        $timeForm->handleRequest($request);
        if($timeForm->isSubmitted() && $timeForm->isValid()){
            $formData = $timeForm->getData();
            $result = $this->get('form_handler_service')->timeContractStatistics($formData,$em);


            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate($result['result'], /*or query NOT result */
                $request->query->getInt('page', 1)/*page number*/,20000/*limit per page*/);

            //convert data to url format
            $formData['all'] = $formData['all'] === true ? 1 : 0;
            $formData['start'] = $formData['start'] === null ? 0 : $formData['start']->format('d-m-Y');
            $formData['end'] = $formData['end'] === null ? 0 : $formData['end']->format('d-m-Y');
            $formData['criteria'] = $formData['criteria'] === null ? 0 : $formData['end'];

            return $this->render('@Rozz/Contracts/contract_statistics.html.twig', ['pagination' => $pagination,
                                                                                'searchForm'=>$timeForm->createView(),
                                                                                'chartdata'=>$result['chart'],
                                                                                'formData' => $formData
                                                                                ]);

        }
        return $this->render('@Rozz/Contracts/contract_statistics.html.twig',['pagination'=>false,
                                                                        'searchForm'=>$timeForm->createView(),
                                                                        ]);
    }



    /**
     * @Route("/contact/statistics/download/{all}/{start}/{end}/{criteria}", name="contract_download_statistics")
     */
    public function downloadStatisticsAction($all, $start, $end, $criteria = null)
    {
        $formData['all'] = $all == 0 ? false : true;
        $formData['start'] = $start == 0 ? null : date_create_from_format('d-m-Y', $start);
        $formData['end'] = $end == 0 ? null : date_create_from_format('d-m-Y', $end);
        $formData['criteria'] = $criteria == 0 ? null : $criteria;

        $em = $this->getDoctrine()->getManager();
        $result = $this->get('form_handler_service')->timeContractStatistics($formData,$em);
        //направи екселов файл с резултатите

        $userName = $this->getUser()->getUserName();
        $excelFileName = 'stats_' . $userName . '_results.xls';

        $excelFilePath = $this->getParameter('exl_dir') . $excelFileName;
        $this->get('excel_service')->getContractsStatistics($result, $excelFilePath);

        return $this->file($excelFilePath);
    }

    /**
     * @param $id
     * @Route("/contract/number/{id}", name="contract_add_number")
     */
    public function addNumberAction($id, Request $request){
        $em = $this->getDoctrine()->getManager();
        $contract = $em->getRepository('RozzBundle:Contracts')->find($id);
        $formData = ['num'=>null,'date'];
        $form = $this->createFormBuilder($formData)
            ->getForm()
            ->add('num', TextType::class,['label'=>'№ '])
            ->add('date', TextType::class,['label'=>'/']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $formData = $form->getData();
            $contract->setNum($formData['num'].'/'.$formData['date']);
            $em->persist($contract);
            $em->flush();
            return $this->redirectToRoute('contract_view',['id'=>$id]);
        }
        return $this->render('@Rozz/Contracts/contract_add_num_form.html.twig',['form'=>$form->createView()]);
    }
}

