<?php
/**
 * Created by PhpStorm.
 * User: Acer
 * Date: 2.5.2017 г.
 * Time: 23:09
 */

namespace RozzBundle\Services;


use Doctrine\ORM\EntityManager;
use RozzBundle\Entity\Doc;
use RozzBundle\Entity\Kat;
use RozzBundle\Entity\Lands;
use RozzBundle\Entity\Mest;
use RozzBundle\Entity\Ntp;
use RozzBundle\Entity\Zem;

class CsvService
{
    public function checkCsv(\Symfony\Component\HttpFoundation\File\UploadedFile $uploadedCsv, EntityManager $entityManager)
    {
        $mestNames = [];
        $zemsNames = [];
        $katNames = [];
        $ntpNames = [];

        $mests = $entityManager->getRepository(Mest::class)->findAll();
        $zems = $entityManager->getRepository(Zem::class)->findAll();
        $kats = $entityManager->getRepository(Kat::class)->findAll();
        $ntps = $entityManager->getRepository(Ntp::class)->findAll();

        foreach ($mests as $mest){
            $mestNames[] = $mest->getName();
        }
        foreach ($zems as $zem){
            $zemsNames[]= $zem->getName();
        }
        foreach ($kats as $kat){
            $katNames[]= $kat->getName();
        }
        foreach ($ntps as $ntp){
            $ntpNames[] = $ntp->getName();
        }

        $warnings=[];
        $errors=[];
        $rowCount = 0;
        $lands =[];

        $content = file_get_contents($uploadedCsv);
        $content =iconv("Windows-1251//IGNORE","UTF-8",$content);
        $landsArray = explode(PHP_EOL,$content);
        foreach ($landsArray as $index=>$land){

            $rowCount++;
            $row = $index+1;
            $landArray = explode(';',$land);

            if (count($landArray) >= 7){
//TO DO land overwrite check!!!
//                if(strlen($landArray[0])!= 6 && strpos($landArray[0], '.') == false ){
//                    $errors[]='Грешка в ред '.$row.'! Номера на имота трябва да бъде с 6 цифри! въведен е номер "'.$landArray[0].'"';
//                }
//                if(strpos($landArray[0], '.') !== false){
//                    $numberArray = explode('.',$landArray[0]);
//                    $newNumber='';
//                    foreach ($numberArray as $partNumber){
//                        if (strlen($partNumber)==1){
//                            $partNumber = '00'.$partNumber;
//                            $newNumber.=$partNumber;
//                        }elseif (strlen($partNumber)==2){
//                            $partNumber = '0'.$partNumber;
//                            $newNumber.=$partNumber;
//                        }elseif (strlen($partNumber)==3){
//                            $newNumber.=$partNumber;
//                        }else{
//                            $errors[]='Грешка в ред '.$row.'! Номера на имота трябва да бъде с не повече от 3 цифри преди и след точката! въведен е номер "'.$landArray[0].'"';
//                        }
//                    }
//                    $landArray[0]=$newNumber;
//                }
                if (!in_array($landArray[1],$mestNames) ){
                    $warnings[]='Внимание! На имот '.$landArray[0].' (ред: '.$row.') е зададена местност, която не присъства в базата данни!';
                }
                if (!in_array($landArray[2],$zemsNames) ){
                    $warnings[]='Внимание! На имот '.$landArray[0].' (ред: '.$row.') е зададено землище, което не присъства в базата данни!';
                }
                if (!in_array($landArray[3],$ntpNames) ){
                    $warnings[]='Внимание! На имот '.$landArray[0].' (ред: '.$row.') е зададено НТП, което не присъства в базата данни!';
                }
                if (!in_array($landArray[4],$katNames) ){
                    $warnings[]='Внимание! На имот '.$landArray[0].' (ред: '.$row.') е зададена категория, която не присъства в базата данни!';
                }
                if(!is_numeric($landArray[5])){
                    $errors[]='Грешка в ред '.$row.'! Невалидна площ на имот "'.$landArray[0].'"';
                }
                $lands[]=$landArray;
            }elseif (count($landArray) == 1){
                $result['lands']=$lands;
                $result['errors']=$errors;
                $result['warnings']=$warnings;
                $result['rowCount']= $rowCount;
                return $result;
            }else{
                $result['lands']=$lands;
                $errors[]='Грешка в ред '.$row.'! Въведени са по-малко данни за имот "'.$landArray[0].'"';
                $result['errors']=$errors;
                $result['warnings']=$warnings;
                $result['rowCount']= $rowCount;
                return $result;
            }
        }
        $result['lands']=$lands;
        $result['errors']=$errors;
        $result['warnings']=$warnings;
        $result['rowCount']= $rowCount;
        return $result;

    }


    public function addLands(Array $checkedLandsArray, EntityManager $em)
    {
        ini_set('max_execution_time', 0);
        foreach ($checkedLandsArray as $landArray){
            $landEntity = new Lands();
            $landEntity->setNum($landArray[0]);
            $mest = $em->getRepository(Mest::class)->findOneBy(['name'=>$landArray[1]]);
            if($mest == null){
                $mest = new Mest();
                $mest->setName($landArray[1]);
                $landEntity->setMest($mest);
            }else{
                $landEntity->setMest($mest);
            }
            $zem = $em->getRepository(Zem::class)->findOneBy(['name'=>$landArray[2]]);
            if($zem == null){
                $zem = new Zem();
                $zem->setName($landArray[2]);;
                $landEntity->setZem($zem);
            }else{
                $landEntity->setZem($zem);
            }
            $ntp = $em->getRepository(Ntp::class)->findOneBy(['name'=>$landArray[3]]);
            if($ntp == null){
                $ntp = new Ntp();
                $ntp->setName($landArray[3]);
                $landEntity->setNtp($ntp);
            }else{
                $landEntity->setNtp($ntp);
            }
            $kat = $em->getRepository(Kat::class)->findOneBy(['name'=>$landArray[4]]);
            if($kat == null){
                $kat = new Kat();
                $kat->setName($landArray[4]);
                $landEntity->setKat($kat);
            }else{
                $landEntity->setKat($kat);
            }

            $landEntity->setArea($landArray[5]);

            $doc = $em->getRepository(Doc::class)->findOneBy(['name'=>$landArray[6]]);
            if($doc == null){
                $doc = new Doc();
                $doc->setName($landArray[6]);
                $landEntity->setDoc($doc);
            }else{
                $landEntity->setDoc($doc);
            }
            $em->persist($landEntity);
            $em->flush();
            $em->clear();
        }

    }
}