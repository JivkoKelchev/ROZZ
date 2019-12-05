<?php
/**
 * Created by PhpStorm.
 * User: Acer
 * Date: 24.7.2017 г.
 * Time: 17:26
 */

namespace RozzBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use RozzBundle\Entity\ApplicationSettings;
use RozzBundle\Entity\Contracts;
use RozzBundle\Entity\Lands;
use RozzBundle\Entity\NewContracts;
use RozzBundle\Entity\SelectedLand;
use RozzBundle\Entity\UsedArea;
use RozzBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormBuilder;


use Symfony\Component\Form\Extension\Core\Type\NumberType;


use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ContractService
{
//statusArray
//active=>1
//has_annex=>2
//expired=>3







    //$this->container->getParameter('contracts_status_array');
    //достъп до Parameters
    private $container;
    private $landService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function createSelectedLandsForm( FormBuilder $fb, $selectedLands, NewContracts $newContract)
    {
        foreach ($selectedLands as $index=>$selectedLand){
            /**
             * @var SelectedLand $selectedLand
             */
            $land = $selectedLand->getLand();
            $maxArea = $this->calculateLandFreeAreaForSelectedLands($selectedLand->getLand(), $newContract);

            $fb->add($index, NumberType::class,
                ['label'=>'№ '.$land->getNum().' ['.$land->getArea().'  дка/ '.$maxArea.' свободна]',
                    'constraints'=>[new NotBlank(), new Range(['max'=>$maxArea, 'maxMessage'=>'Максималната свободна площ е '.$maxArea.'!'])],
                    'attr'=>['value'=>$selectedLand->getArea()]
                ])
                ->add($index+100, NumberType::class,['label' => 'Цена за дка за НТП : '.$land->getNtp()->getName(),
                    'constraints'=>new NotBlank(),
                    'data' => $selectedLand->getPrice()
                ]);
        }

        return $fb;
    }

    public function clearNewContractAndSelected(\Doctrine\Common\Persistence\ObjectManager $em, User $user)
    {
        $selectedLands = $em->getRepository(SelectedLand::class)->findBy(['user'=>$user]);
        $newContract = $em->getRepository(NewContracts::class)->findOneBy(['user'=>$user]);

        $em->remove($newContract);
        foreach ($selectedLands as $land){
            $em->remove($land);
        }
        $em->flush();
    }
    public function setNewContractForEdit(\Doctrine\Common\Persistence\ObjectManager $em, User $user, $contractId)
    {
        $this->setNewContractFromExistingContract($em, $user, $contractId);
        $newContract = $em->getRepository(NewContracts::class)->findOneBy(['user'=>$user]);
        $newContract->setType(4);
        $newContract->setEditContractId($contractId);
        $em->persist($newContract);
        $em->flush();
    }

    public function setNewContractFromExistingContract(\Doctrine\Common\Persistence\ObjectManager $em, User $user, $contractId)
    {
        $contract = $em->getRepository(Contracts::class)->find($contractId);
        $newContract = $em->getRepository(NewContracts::class)->findOneBy(['user'=>$user]);
        if ($newContract == null){
            $newContract = new NewContracts();
            $newContract->setUser($user);
        }

        $newContract->setApplication($contract->getApplication());
        $newContract->setResheniq($contract->getResheniq());
        $newContract->setStart($contract->getStart());
        $newContract->setExpire($contract->getExpire());
        $newContract->setHolder($contract->getHolder());
        $newContract->setExaminer($contract->getExaminer());
        $newContract->setExaminers($contract->getExaminers());
        $newContract->setReason($contract->getReason());
        if ($contract->getNeighbours() != null) {
            $newContract->setNeighbours($contract->getNeighbours());
        }
        $newContract->setAnnexContractId($contract->getId());
        $em->persist($newContract);
        $em->flush();

        $this->setSelectedLandsFromExistingContract($em,$user,$contractId);
    }

    public function setSelectedLandsFromExistingContract( EntityManager $em, $user, $contractId)
    {
        $selectedLands = $em->getRepository(SelectedLand::class)->findBy(['user'=>$user]);
        if ($selectedLands != null){
            foreach ($selectedLands as  $land){
                $em->remove($land);
                $em->flush();
            }
        }
        $contract = $em->getRepository(Contracts::class)->find($contractId);
        $usedAreas = $em->getRepository(UsedArea::class)->findBy(['contract'=>$contract]);
        foreach ($usedAreas as $usedArea){
            $selectedLand = new SelectedLand();
            $selectedLand->setArea($usedArea->getArea());
            $selectedLand->setLand($usedArea->getLand());
            $selectedLand->setPrice($usedArea->getPrice());
            $selectedLand->setUser($user);
            $em->persist($selectedLand);
            $em->flush();
        }
    }

    public function getAnnexDifference($annex,ObjectManager $em, User $user)
    {
        $contract = $em->getRepository(Contracts::class)->find($annex->getAnnexContractId());
        $haveDifference = false;

        $annexHtml = '<p style="margin-left: 15px"><strong>Настоящият анекс към договор № '.$contract->getNum().' предвижда следните изменения :</strong></p>
        
                        <ul>';

        $usedAreasContract = $em->getRepository(UsedArea::class)->findBy(['contract'=>$contract]);

        if (get_class($annex) == NewContracts::class){
            $usedAreasAnnex = $em->getRepository(SelectedLand::class)->findBy(['user'=>$user]);
        }else{
            $usedAreasAnnex = $em->getRepository(UsedArea::class)->findBy(['contract'=>$annex]);
        }

        $sameLands = [];
        //за всички селектирани проверявамв дали ги има в стария договор
        for ($i = 0; $i <= count($usedAreasAnnex)-1; $i++){
            for ($j = 0; $j <= count($usedAreasContract)-1; $j++){

                if ($usedAreasAnnex[$i]->getLand() == $usedAreasContract[$j]->getLand()) {
                    $sameLands[$i]=$j;
                    if ($usedAreasAnnex[$i]->getArea() !== $usedAreasContract[$j]->getArea()){
                        $annexHtml = $annexHtml.'<li>Променена площ за имот №'.'</li>';
                        $haveDifference = true;
                    }
                    if ($usedAreasAnnex[$i]->getPrice() !== $usedAreasContract[$j]->getPrice()){
                        $annexHtml = $annexHtml.'<li>Променена цена за имот №'.$usedAreasAnnex[$i]->getLand()->getNum().'</li>';
                        $haveDifference = true;
                    }
                }else {
                    continue;
                }

            }
        }

        if ($sameLands){
            for ($i = 0; $i <= count($usedAreasAnnex)-1; $i++) {
                if (array_key_exists($i,$sameLands)){
                    continue;
                }
                else{
                    $haveDifference = true;
                    $annexHtml = $annexHtml.
                        '<li>Добавен имот №'.$usedAreasAnnex[$i]->getLand()->getNum().'</li>';
                }
            }
            for ($j = 0; $j <= count($usedAreasContract)-1; $j++){
                if (in_array($j,$sameLands)){
                    continue;
                }
                else{
                    $haveDifference = true;
                    $annexHtml = $annexHtml.
                        '<li>Премахнат имот №'.$usedAreasContract[$j]->getLand()->getNum().'</li>';
                }
            }
        }else{
            //TODO : Какво правим, когато всички имоти са различни?
//            dump('Всички имоти са различни !');
        }

        if ($annex->getResheniq() != $contract->getResheniq()){
            $haveDifference = true;
            $annexHtml = $annexHtml.
                '<li> Променено решение : '.$annex->getResheniq().'</li>';
        }
        if ($annex->getApplication() != $contract->getApplication()){
            $haveDifference = true;
            $annexHtml = $annexHtml.
                '<li> Променено заявлвние : '.$annex->getApplication().'</li>';
        }
        if ($annex->getStart() != $contract->getStart()){
            $annexHtml = $annexHtml.
                '<li> Променена начална дата : '.date_format($annex->getStart(),"d.m.Y").'г.</li>';
            $haveDifference = true;
        }
        if ($annex->getExpire() != $contract->getExpire()){
            $annexHtml = $annexHtml.
                '<li> Променена крайна дата : '.date_format($annex->getExpire(),"d.m.Y").'г.</li>';
            $haveDifference = true;
        }

        $annexHtml = $annexHtml . '</ul>';
        if ($haveDifference){
            return $annexHtml;
        }else{
            return '';
        }
    }


    // Check!!!
    public function checkDataForContract(User $user, EntityManager $em)
    {
        $selectedLands = $em->getRepository('RozzBundle:SelectedLand')->findBy(['user'=>$user]);
        $newContract = $em->getRepository('RozzBundle:NewContracts')->findOneBy(['user'=>$user]);
        if(!$selectedLands or !$newContract){
            return false;
        }
        //something is not filled
        elseif(
            !$newContract->getExpire() ||
            !$newContract->getApplication() ||
            !$newContract->getResheniq() ||
            !$newContract->getExaminers() ||
            !$newContract->getHolder())
        {
            return false;
        }else{
            return true;
        }
    }

    public function persistContract(User $user, \Doctrine\Common\Persistence\ObjectManager $em, String $templateDir)
    {
        //Данни за създаването на договор
        $selectedLands = $em->getRepository('RozzBundle:SelectedLand')->findBy(['user'=>$user]);
        $newContract = $em->getRepository('RozzBundle:NewContracts')->findOneBy(['user'=>$user]);

        if (!$selectedLands || !$newContract ){
            throw new \Exception('Не са избрани имоти или не са попълнени данни за договора!');
        }else{
            //todo : Здължително да се проверява дали избраните имоти не са с по-голяма площ от свободната им
            //todo : Да се провери дали се вкарва договор с крайна дата по-ранна от днещната и да се извежда предопреждение!!!!
            //Настояшия кмет
            $mayor = $em->getRepository('RozzBundle:Mayors')->findOneBy(['status'=>1]);

            if ($newContract->getEditContractId()){
                $contract = $em->getRepository('RozzBundle:Contracts')->find($newContract->getEditContractId());
            }else{
                $contract = new Contracts();
            }

            $contract->setApplication($newContract->getApplication());
            $contract->setResheniq($newContract->getResheniq());

            $today = new \DateTime();

            $contract->setReason($newContract->getReason());

            $contract->setDate( new \DateTime($today->format('Y-m-d')));

            $contract->setStart($newContract->getStart());

            $contract->setExpire($newContract->getExpire());

            //Todo after delivery work only with examiners!!!
            //$contract->setExaminer($newContract->getExaminer());

            $contract->setExaminers($newContract->getExaminers());

            $contract->setHolder($newContract->getHolder());

            $contract->setUser($user);

            $contract->setMayor($mayor);

            if ($newContract->getNeighbours()){
                $contract->setNeighbours($newContract->getNeighbours());
            }

            $contract->setType($newContract->getType());

            //ако договора е анекс
            if ($newContract->getType() == 3){

                if ($newContract->getAnnexContractId()){
                    //задавам AnnexContractId (ид на договора към който е настоящия анекс)
                    $contract->setAnnexContractId($newContract->getAnnexContractId());
                    //задавам status=>2 на договора към който е настоящия анекс)
                    $oldContract = $em->getRepository(Contracts::class)->find($newContract->getAnnexContractId());
                    $oldContract->setStatus(2);
                    //отменям старите площи
                    $usedAreas = $oldContract->getUsedArea();
                    foreach ($usedAreas as $area){
                        $area->setActive(0);
                        $em->persist($area);
                        $em->flush();
                    }

                }else{
                    throw new \Exception('Не е избран договор за настоящия анекс!');
                }
            }
            $contract->setStatus(1);
            $em->persist($contract);
            $em->flush();

            //Използвани плошщи
            if($contract->getUsedArea() and $contract->getType() == 4){
                $usedAreaCollection = $contract->getUsedArea();
                foreach ($usedAreaCollection as $usedArea){
                    $em->remove($usedArea);
                }
                $em->flush();
            }
            foreach ($selectedLands as $land){
                $usedArea = new UsedArea();
                $usedArea->setArea($land->getArea());
                $usedArea->setContract($contract);
                $usedArea->setLand($land->getLand());
                $usedArea->setPrice($land->getPrice());
                $usedArea->setActive(1);
                $em->persist($usedArea);
                $em->remove($land);
                $em->flush();
            }

            $em->remove($newContract);
            $em->flush();

            $id = $contract->getId();
            return $id;
        }

    }

    private function populateRtf($vars, $doc_file)
    {

        $replacements = array ('\\' => "\\\\",
            '{'  => "\{",
            '}'  => "\}");

        $document = file_get_contents($doc_file);
        if(!$document) {
            return false;
        }

        foreach($vars as $key=>$value) {
            $search = "%".$key."%";
            $document = str_replace($search, iconv("UTF-8","Windows-1251//IGNORE",$value), $document);
        }

        return $document;
    }

    public function createRtf(Contracts $contractEntity, $templateDir)
    {
        //variables for populating RTF
        $reason = $contractEntity->getReason();
        $reshenie = $contractEntity->getResheniq();
        $application = $contractEntity->getApplication();
        $mayor = $contractEntity->getMayor()->getName();
        $holder =$contractEntity->getHolder()->getName();

        $landString = '';
        $usedArea = $contractEntity->getUsedArea();

        $i=1;
        $iForAreas = 0;
        $price = 0;

        $totalArea = 0;
        foreach ($usedArea as $area){
            $landString.=
                $i.". Поземлен имот № ".$area->getLand()->getNum().", м.”".$area->getLand()->getMest()->getName()."”, землище ".$area->getLand()->getZem()->getName().", общ. Велинград, с НТП ".$area->getLand()->getNtp()->getName()." и площ ".$area->getArea()." дка. }{\pard\par}{\\rtlch\\fcs1 \\af0\\afs28 \\ltrch\\fcs0 \\fs28\\insrsid4392819 ";
            $price = $area->getArea()*$area->getPrice();

            $totalArea = $totalArea + $area->getArea();
            $i++;
            $iForAreas++;

        }
        $landString .= 'С обща площ '.$totalArea.' дка.';

        $start = $contractEntity->getStart();
        if ($start == null){
            /**
             * @var \DateTime $endDate
             */
            $endDate = $contractEntity->getExpire();
            $start = clone $endDate;
            $start->modify('-1 year')->modify('+1 day');
            $contractEntity->setStart($start);
        }

        $term = $contractEntity->getExpire()->diff($start);
        $start = $start->format('d-m-Y');
        $end = $contractEntity->getExpire()->format('d-m-Y');

        $term = $term->y;
        if ($term <= 1 ){
            $term = $term.' стопанска година';
        }elseif ($term > 1){
            $term = $term.' стопански години';
        }

        $creator = $contractEntity->getUser()->getName();

        //Todo make to work with examiners array collection!!
//        $examiner = $contractEntity->getExaminer()->getName();
        $examiner = 'test';

        $vars = ['reshenie'=>$reshenie,
            'application'=>$application, 'mayor'=>$mayor,
            'holder'=>$holder, 'lands'=>$landString,
            'start'=>$start, 'end'=>$end, 'term' => $term,
            'creator'=>$creator,
            'examiner' =>$examiner,
            'price' => $price];

        $newString=$this->populateRtf($vars,$templateDir.'rtf.rtf');;
        $uniqueDateTime = new \DateTime('now');
        $fileName = $uniqueDateTime->format('YmdHis').'.rtf';

        $file = fopen($templateDir.'/rtf_files/'.$fileName, "w");

//////////// Open the file to get existing content
        $current = file_get_contents($templateDir.'/rtf_files/'.$fileName);
////////////// Append a new person to the file
        $current .= $newString;
////// Write the contents back to the file
        file_put_contents($templateDir.'/rtf_files/'.$fileName, $current);

        return $templateDir.'/rtf_files/'.$fileName;
    }

    public function calculateLandFreeAreaForSelectedLands(Lands $land, NewContracts $newContract)
    {
        $usedAreaCollection = $land->getUsedArea();
        if ($usedAreaCollection){
            $usedArea = 0;
            //Площ на всички активни договори
            foreach ($usedAreaCollection as $usedAreaEntity){

                if ($usedAreaEntity->getActive() == 1)  {
                    $usedArea += $usedAreaEntity->getArea();

                    //ако редактираме или правим анекс добавяме използваната площ от предходния договор
                    if ($newContract->getType() == 3 || $newContract->getType() == 4){
                        $usedArea -= $usedAreaEntity->getArea();
                    }
                }

            }
            return $land->getArea() - $usedArea;
        }else{
            return $land->getArea();
        }
    }

    public function isContractActive(ObjectManager $em, $contractId){
        $appSettings = $em->getRepository(ApplicationSettings::class)->find(1);
        $beginningOfAgroYear = $appSettings->getAgroYear();
        $endOfAgroYear = new \DateTime($beginningOfAgroYear->format('Y-m-d'));
        $endOfAgroYear->add(new \DateInterval('P1Y'))->sub(new \DateInterval('P1D'));
        $contract = $em->getRepository(Contracts::class)->find($contractId);
        $expire = $contract->getExpire();
        $start = $contract->getStart();

        //ако не е изтекъл срока и ако няма анекс
        if($start<= $beginningOfAgroYear && $expire>= $endOfAgroYear && $contract->getStatus()!= 2){
            return True;
        }else{
            return False;
        }
    }
}