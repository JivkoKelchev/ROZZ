<?php

namespace RozzBundle\Controller;

use RozzBundle\Entity\ApplicationSettings;
use RozzBundle\Entity\Examiners;
use RozzBundle\Entity\Mayors;
use RozzBundle\Form\ExaminerType;
use RozzBundle\Form\MayorType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class SettingsController extends Controller
{
    /**
     * @Route("/settings")
     */
    public function settingsIndexAction(){
        return $this->render("@Rozz/SettingsView/settings_index.html.twig");
    }

    /**
     * @Route("/settings/db/dump", name="db_dump")
     */
    public function dbDumpAction(){
        $host = $this->getParameter('database_host');
        $user = $this->getParameter('database_user');
        $pass = $this->getParameter('database_password');
        $name = $this->getParameter('database_name');
        $time = new \DateTime('now');
        $timeString = $time->format('YmdHis');
        $fileName = $this->get('kernel')->getRootDir().'/../web/files/sql_files/'.$timeString.'.sql';
        $output = $this->get("db_backup_service")->dump($fileName);
        if(empty($output) && file_exists($fileName)){
            $response = new BinaryFileResponse($fileName);
            $mimeTypeGuesser = new FileinfoMimeTypeGuesser();

            if($mimeTypeGuesser->isSupported()){
                // Guess the mimetype of the file according to the extension of the file
                $response->headers->set('Content-Type', $mimeTypeGuesser->guess($fileName));
            }else{
                // Set the mimetype of the file manually, in this case for a text file is text/plain
                $response->headers->set('Content-Type', 'text/plain');
            }

            // Set content disposition inline of the file
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $timeString.'.sql'
            );

            return $response;
        }
        dump($host,$user,$pass,$name, $output);
        exit;

    }

    /**
     * @Route("/settings/db/import", name="db_import")
     */
    public function dbImportAction(Request $request){
        $fb = $this->createFormBuilder();

        $fb->add('sqlFile', FileType::class, ['label'=>'Избети файл', 'attr'=>['accept'=>'.sql']]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $file stores the uploaded PDF file
            /**
             * @var UploadedFile $file
             */
            $formData =  $form->getData();
            $file = $formData["sqlFile"];
            $output = $this->get("db_backup_service")->import($file->getPathname());
        }

        return $this->render('@Rozz/SettingsView/import_database.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/input", name="input")
     */
    public function inputLandsAction(){
        return $this->render("@Rozz/InputData/Input_data_index.html.twig");
    }

    /**
     * @Route("/select/mayor", name="select_mayor")
     */
    public function selectMayorAction( Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $mayor = $em->getRepository(Mayors::class)->find(1);
        $newMayor = new Mayors();
        $form = $this->createForm(MayorType::class,$newMayor);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if($mayor){
                $mayor->setStatus(0);
                $em->persist($mayor);
            }


            $newMayor->setStatus(1);
            $em->persist($newMayor);
            $em->flush();
            return $this->redirectToRoute('rozz_settings_settingsindex');
        }

        return $this->render('@Rozz/SettingsView/select_mayor.html.twig',['form'=>$form->createView()]);
    }


    /**
     * @param Request $request
     * @Route("/add/examiner", name="add_examiners")
     */
    public function addExaminerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $examiner = new Examiners();
        $form = $this->createForm(ExaminerType::class,$examiner);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $em->persist($examiner);
            $em->flush();
            $msg = 'Добавен/а е '.$examiner->getName().' на позиция'.$examiner->getPosition().' в списъка със съгласували.';
            $this->get('session')->getFlashBag()->add("success",$msg);
            $this->redirectToRoute('add_examiners');
        }

        $allExaminers = $em->getRepository(Examiners::class)->findAll();
        return $this->render('@Rozz/SettingsView/add_examiners.html.twig',['form'=>$form->createView(),'examiners'=>$allExaminers]);
    }

    /**
     * @Route("/delete/examiner/{id}",name="delete_examiner")
     */
    public function deleteExaminerAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $examiner = $em->getRepository(Examiners::class)->find($id);
        $em->clear($examiner);
        $em->flush();

        return $this->redirectToRoute('add_examiners');
    }

    /**
     * @Route("/set-current-agro-year", name="set_current_agro_year")
     */
    public function setCurrentAgroYearAction(Request $request)
    {
       $em = $this->getDoctrine()->getManager();
       $appSettings = $em->getRepository(ApplicationSettings::class)->find(1);
       if (!$appSettings){
           $this->get('app_settings_service')->initAppSettings($em);
       }
       $agroYear = $appSettings->getAgroYear();
       $formData['year'] =  $agroYear;
       $form = $this->createFormBuilder($formData)
           ->add('year', DateType::class,
               ['label' => 'Начало на текуща стопанска година : ',
               'years' => range(date('Y')-10, date('Y') + 15),
               'required' => true])
           ->getForm();
       $form->handleRequest($request);
       if ($form->isSubmitted() and $form->isValid()){
           $formData = $form->getData();
           //Обновява настройката
           $appSettings->setAgroYear($formData['year']);
           $em->persist($appSettings);
           $em->flush();
           if ($appSettings){
               $this->get('session')->set('agroYear',$appSettings->getAgroYear());
           }
           //Обновява активните договори
           $this->get('lands_service')->setUsedAreaForActiveContracts($em, $formData['year']);
       }

       return $this->render('@Rozz/SettingsView/set_current_agro_year.html.twig',['form'=>$form->createView()]);
    }
}
