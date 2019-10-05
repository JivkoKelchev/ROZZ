<?php

namespace RozzBundle\Controller;

use RozzBundle\Entity\Doc;
use RozzBundle\Entity\Kat;
use RozzBundle\Entity\Lands;
use RozzBundle\Entity\Mest;
use RozzBundle\Entity\Ntp;
use RozzBundle\Entity\Zem;
use RozzBundle\Form\LandType;
use RozzBundle\Repository\landsRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class InputDataController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/input/file", name="input_file")
     */
    public function fileInput(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->createNamedBuilder('input_file')
            ->add('file',FileType::class,['label'=>'Избери файл','attr'=>["accept"=>".csv"]])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $csvFile = $form->getData()['file'];
            $check = $this->get('csv_file_service')->checkCsv($csvFile, $em);


            if (empty($check['errors'])){
                $this->get('csv_file_service')->addLands($check['lands'],$em);
                $flashMsg = 'Добавени са '.count($check['lands']).' имота';
                $this->get('session')->getFlashBag()->add('success',$flashMsg);
                return $this->render('@Rozz/InputData/input_file_form.html.twig', array('form' => $form->createView()));
//flash message Warning!

            }else{
//flash message ERROR!!!-print errors,
                $flashMsg = '';
                foreach ($check['errors'] as $error){
                    $flashMsg .= $error.'<br>';
                }
                $this->get('session')->getFlashBag()->add('error',$flashMsg);
                return $this->render('@Rozz/InputData/input_file_form.html.twig', array('form' => $form->createView()));
            }
        }
        return $this->render('@Rozz/InputData/input_file_form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/input/land", name="input_single_land")
     */
    public function singleLandInputAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $mests = $em->getRepository('RozzBundle:Mest')->findAll();
        $zems = $em->getRepository('RozzBundle:Zem')->findAll();
        $ntps = $em->getRepository('RozzBundle:Ntp')->findAll();
        $kats = $em->getRepository('RozzBundle:Kat')->findAll();

        $choices['mests']=$mests;
        $choices['zems']=$zems;
        $choices['ntps']=$ntps;
        $choices['kats']=$kats;

        $land = new Lands();
        $form = $this->createForm(LandType::class,$land,$choices);
        $form->handleRequest($request);
        if($form->isValid() && $form->isSubmitted()){
            if ($land->getNewDoc()!= null){
                $newDoc = new Doc();
                $newDoc->setName($land->getNewDoc());
                $em->persist($newDoc);
                $em->flush();
                $land->setDoc($newDoc);
            }
            if ($land->getNewKat()!=null){
                $newKat = new Kat();
                $newKat->setName($land->getNewKat());
                $em->persist($newKat);
                $land->setKat($newKat);
            }
            if ($land->getNewMest()!= null){
                $newMest = new Mest();
                $newMest->setName($land->getNewMest());
                $em->persist($newMest);
                $em->flush();
                $land->setMest($newMest);
            }
            if ($land->getNewNtp()!= null){
                $newNtp = new Ntp();
                $newNtp->setName($land->getNewNtp());
                $em->persist($newNtp);
                $em->flush();
                $land->setNewNtp($newNtp);
            }
            if ($land->getNewZem()!= null){
                $newZem = new Zem();
                $newZem->setName($land->getNewZem());
                $em->persist($newZem);
                $em->flush();
                $land->setZem($newZem);
            }
            $newDoc = new Doc();
            $newDoc->setName($land->getDoc());
            $em->persist($newDoc);
            $em->flush();
            $land->setDoc($newDoc);
            $em->persist($land);
            $em->flush();
            $msg = "Успешно добавихте имот с №".$land->getNum()." в м.".$land->getMest()->getName().", землището на ".$land->getZem()->getName().
                ", с НТП ".$land->getNtp()->getName()." и заповед/документ : ".$land->getDoc()->getName();
            $this->get('session')->getFlashBag()->add('success',$msg);
            return $this->redirectToRoute('input_single_land');
        }


        return $this->render('@Rozz/InputData/input_single_land.html.twig',['form'=>$form->createView()]);
    }

    /**
     * @Route("/convert")
     * вход от ексел-мкад"шльоковица" запазена като Unicode (UTF-16)
     *  1. копирай данните от excel в notepad и запази файла като proba.csv - unicode в /web/files
     *  2. замести табовете с  точка и запетая (;)
     *  3. отиди на localhost:8000/convert
     *  4. в web/files се генерира файла 123.csv
     *  5. отвори 123.csv с notepad  и го презапиши в ANSI формат
     *  6. отиди на localhost:8000/input/file и зареди 123.csv
     */
    public function readMkadCsv()
    {
        $shlokovica = file_get_contents($this->getParameter('rtf_dir').'encodeNew1.csv');
        $shlokovica= iconv("MIK","UTF-8",$shlokovica);
        $shlokovicaArray = explode(PHP_EOL,$shlokovica);
        $encodeArray = [];
        foreach ($shlokovicaArray as $charString){
            $charArray = explode(';',$charString);
            $encodeArray[$charArray[0]]=$charArray[1];
        }

        $proba = file_get_contents($this->getParameter('rtf_dir').'proba.csv');
        $proba = iconv("UTF-16","UTF-8",$proba);
        foreach ($encodeArray as $key => $value){
            $proba = str_replace($key,$value,$proba);
        }
        $landsArray = explode(PHP_EOL,$proba);
        $inputArray = [];
        $inputString = '';
        foreach ($landsArray as $landString){
            $landArray = explode(';',$landString);
            if ($landArray[8]!= "Държавна частна" && $landArray[8]!= "Частна" &&
                $landArray[8] != "Държавна публична"  &&

                $landArray[3] != "Сграда" && $landArray[4] != ""
                && $landArray[8]!="Вид собственост"
            )
            {
                unset($landArray[2]);
                unset($landArray[3]);
                unset($landArray[8]);
                unset($landArray[9]);
                unset($landArray[11]);
                unset($landArray[12]);
                unset($landArray[13]);
                unset($landArray[14]);
                unset($landArray[15]);
                $landArray[7] = $landArray[7]/1000;
                $outputLandArray = [];
                $orderArray = [1,4,0,5,6,7,10];
                foreach ($orderArray as $index){
                    $outputLandArray[$index]=$landArray[$index];
                }
                foreach ($outputLandArray as $index =>$value){
                    $inputString .= $value.';';
                    if ($index == 10){
                        $inputString.= PHP_EOL;
                    }
                }

                $inputArray [] = $outputLandArray;
            }

        }
        $newCsvFile = fopen($this->getParameter('rtf_dir').'123.csv', "w");
        fwrite($newCsvFile, $inputString);
        fclose($newCsvFile);
        dump($inputString);
        exit;
    }

    /**
     * @Route("/cad" , name="cad")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importCadAction(Request $request)
    {
        /**
         * @var UploadedFile $file
         */
        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('cadFile', FileType::class, ['label'=>'Избери фаил', 'attr'=>['accept'=>'.cad']])
            ->add('submit','Symfony\Component\Form\Extension\Core\Type\SubmitType');
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if($form->isValid() && $form->isSubmitted())
        {
            $formData = $form->getData();
            $file = $formData['cadFile'];
            $this->get('cad_service')->import($file->getPathname());
        }

        return $this->render('@Rozz/SettingsView/import_cad.html.twig', ['form'=>$form->createView()]);
    }
}

