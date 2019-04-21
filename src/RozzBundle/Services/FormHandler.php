<?php
/**
 * Created by PhpStorm.
 * User: Acer
 * Date: 14.5.2017 г.
 * Time: 13:24
 */

namespace RozzBundle\Services;


use Doctrine\ORM\EntityManager;
use function PHPSTORM_META\type;
use RozzBundle\Entity\Holders;
use RozzBundle\Entity\Lands;
use RozzBundle\Entity\Mest;
use RozzBundle\Entity\NewContracts;
use RozzBundle\Entity\SelectedLand;
use RozzBundle\Entity\User;
use RozzBundle\Entity\Zem;

class FormHandler
{
    public function landFilter(array $formData, EntityManager $em)
    {
        $qb = $em->getRepository(Lands::class)->createQueryBuilder('l');
        $qb->select();
        $queryParameters = [];
        if ($formData['num'] != null){
            $qb->andWhere('l.num = ?0');
            $queryParameters['0']= $formData['num'];
        }
        if ($formData['mest'] != null){
            $qb->andWhere('l.mest = ?1');
            $mest = $em->getRepository(Mest::class)->findOneBy(['name' => $formData['mest']]);
            $queryParameters['1']= $mest;
        }
        if ($formData['zem'] != null ){
            $qb->andWhere('l.zem = ?2');
            $zem = $em->getRepository(Zem::class)->findOneBy(['name' => $formData['zem']]);
            $queryParameters['2']= $zem;
        }
        $lands = $qb -> setParameters(  $queryParameters )
            ->leftJoin('l.usedArea', 'a')
            ->orderBy('l.mest', 'ASC')
            ->orderBy('a.area', 'DESC')
            ->setMaxResults(20)
            ->getQuery();
        return $lands;
    }




    public function selectHolder(array $formData, EntityManager $em)
    {
        if($formData['egn'] && !$formData['names'])
        {
            $holder = $em->getRepository(Holders::class)->findOneBy(['eGN'=>$formData['egn']]);
            if($holder){
                return $holder;
            }else{
                //there is no holder with such an EGN
                $error ['error'] = 'Няма наемател с такова ЕГН!';
                return $error;
            }
        }
        elseif(!$formData['egn'] && $formData['names'])
        {
            $holder = $em->getRepository(Holders::class)->findOneBy(['name'=>$formData['names']]);
            return $holder;
        }
        elseif(!$formData['egn'] && !$formData['names'])
        {
            //no data
            $error ['error'] = 'Трябва да изберете Наемател!';
            return $error;
        }
        else
        {
            $holder = $em->getRepository(Holders::class)->findOneBy(['name'=>$formData['names']]);
            if($holder->getEGN()!= $formData['egn'])
            {
                //this holder is with different EGN
                $error ['error'] = 'Избрали сте Име на Наемател, който е с различно ЕГН от въведеното!';
                return $error;
            }else{
                return $holder;
            }

        }
    }

    public function createContract(NewContracts $newContract, EntityManager $em)
    {
//        if ($newContract->getType() != 3){
//
//        }
//        $examiner = $em->getRepository("RozzBundle:Examiners")->findOneBy(['name'=>$formData['examiner']]);
//        $newContract = $em->getRepository("RozzBundle:NewContracts")->findOneBy(["user"=>$user]);
//        if (!$newContract){
//            $newContract = new NewContracts();
//            $newContract->setUser($user);
//        }
//        $newContract->setResheniq($formData['reshenie']);
//        $newContract->setApplication($formData['application']);
//
//        if($user->getNewContract()->getType()==2){
//            $thisYear = new \DateTime('now');
//            $date = new \DateTime('30.09.'.$thisYear->format('Y'));
//            $dateInterval = new \DateInterval('P'.$formData['dates'].'Y');
//            $date->add($dateInterval);
//            $newContract->setExpire($date);
//
//        }else{
//            $newContract->setExpire($formData['dates']);
//        }
//
//        $newContract->setExaminer($examiner);
//        $em->persist($newContract);
//        $em->flush();
    }

    public function createContractMY(array $formData, EntityManager $em, User $user)
    {
        $examiner = $em->getRepository("RozzBundle:Examiners")->findOneBy(['name'=>$formData['examiner']]);
        $newContract = $em->getRepository("RozzBundle:NewContracts")->findOneBy(["user"=>$user]);
        if (!$newContract){
            $newContract = new NewContracts();
            $newContract->setUser($user);
        }
        $newContract->setResheniq($formData['reshenie']);
        $newContract->setApplication($formData['application']);
        $date = new \DateTime('30.09.'.$formData['startYear']+$formData['dates']);
        $newContract->setExpire($date);

        $newContract->setExaminer($examiner);
        $em->persist($newContract);
        $em->flush();
    }

    public function searchContract(array $formData, EntityManager $em)
    {
        $contractsRepo = $em->getRepository('RozzBundle:Contracts');
        if ($formData['search']==null){
            $query = $contractsRepo->createQueryBuilder('c')
                ->select()->getQuery();
            $result = $query->getResult();
            $array['query']  = $query;
            $array['result'] = $result;
            return $array;
        }
        $query = $contractsRepo->createQueryBuilder('c')
            ->select()
            ->leftJoin('c.holder','h')
            ->leftJoin('c.usedArea','a')
            ->leftJoin('a.land','l')
            ->leftJoin('l.mest','m')
            ->leftJoin('l.zem','z')
            ->where('c.num LIKE :search')
            ->orWhere('h.name LIKE :search')
            ->orWhere('l.num LIKE :search')
            ->orWhere('m.name LIKE :search')
            ->orWhere('z.name LIKE :search')
            //po mestnost, nomer imot, zemlishte
            ->setParameter('search','%'.$formData['search'].'%')
            ->getQuery();
        $result = $query->getResult();
        $array['query']  = $query;
        $array['result'] = $result;
        return $array;
    }

    public function timeContractStatistics(array $formData, EntityManager $em)
    {
        $now = new \DateTime('now');
        $contractsRepo = $em->getRepository('RozzBundle:Contracts');
        if(!$formData['all']){//не само активните(всички)
            $query = $contractsRepo->createQueryBuilder('c')
                ->select()
                ->leftJoin('c.holder','h')
                ->leftJoin('c.usedArea','a')
                ->leftJoin('a.land','l')
                ->leftJoin('l.mest','m')
                ->leftJoin('l.zem','z')
                ->where('c.date >= :start')
                ->andWhere('c.date <= :end')
                ->andWhere('c.num LIKE :search OR h.name LIKE :search OR l.num LIKE :search OR m.name LIKE :search OR z.name LIKE :search')
                ->setParameter('search','%'.$formData['criteria'].'%')
                ->setParameter('start', $formData['start'])
                ->setParameter('end', $formData['end'])
                ->getQuery();
            $result['result'] = $query->getResult();
            $chartData = [];
            foreach ($result['result'] as $contract){
                $usedArea = $contract->getUsedArea();
                foreach ($usedArea as $area){
                    $land = $area->getLand();
                    if( array_key_exists($land->getNtp()->getName(),$chartData)){
                        $chartData[$land->getNtp()->getName()] = $chartData[$land->getNtp()->getName()] + $area->getArea();
                    }else{
                        $chartData[$land->getNtp()->getName()] = $area->getArea();
                    }
                }
            }
            $result['chart']=$chartData;
            return $result;
        }else{
            $query = $contractsRepo->createQueryBuilder('c')
                ->select()
                ->leftJoin('c.holder','h')
                ->leftJoin('c.usedArea','a')
                ->leftJoin('a.land','l')
                ->leftJoin('l.mest','m')
                ->leftJoin('l.zem','z')
                ->Where('c.date >= :start')->setParameter('start', $formData['start'])
                ->andWhere('c.date <= :end')->setParameter('end', $formData['end'])
                //заявка за активни
                ->andWhere('a.active = 1')

                ->andWhere('c.num LIKE :search OR h.name LIKE :search OR l.num LIKE :search OR m.name LIKE :search OR z.name LIKE :search')
                ->setParameter('search','%'.$formData['criteria'].'%')
                ->getQuery();
            $result['result'] = $query->getResult();
            $chartData = [];
            foreach ($result['result'] as $contract){
                $usedArea = $contract->getUsedArea();
                foreach ($usedArea as $area){
                    $land = $area->getLand();
                    if( array_key_exists($land->getNtp()->getName(),$chartData)){
                        $chartData[$land->getNtp()->getName()] = $chartData[$land->getNtp()->getName()] + $area->getArea();
                    }else{
                        $chartData[$land->getNtp()->getName()] = $area->getArea();
                    }
                }
            }
            $result['chart']=$chartData;
            return $result;
        }
    }
}