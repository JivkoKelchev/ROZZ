<?php
/**
 * Created by PhpStorm.
 * User: Jivko
 * Date: 6.10.2018 г.
 * Time: 21:27
 */

namespace RozzBundle\Services;


use Doctrine\Common\Persistence\ObjectManager;
use RozzBundle\Entity\Contracts;
use RozzBundle\Entity\Lands;
use RozzBundle\Entity\UsedArea;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RozzBundle\Services\ContractService;

class LandsService
{
    //$this->container->getParameter('contracts_status_array');
    //достъп до Parameters
    private $container;
    private $contractService;

    public function __construct(ContainerInterface $container , ContractService $contractService)
    {
        $this->container = $container;
        $this->contractService = $contractService;
    }

    public function getLandFreeArea(ObjectManager $em , $landId){
        $land = $em->getRepository(Lands::class)->find($landId);
        $activeUsedArea = 0;
        $usedArea = $land->getUsedArea();
        foreach ($usedArea as $area){
            /**
             * @var UsedArea $area
             */
            //Ако площта е активна
            if($area->getActive() == 1){
                $activeUsedArea += $area->getArea();
            }
        }

        return $land->getArea() - $activeUsedArea;
    }

    public function setUsedAreaForActiveContracts( ObjectManager $em ,\DateTime $startOfAgroYear){
        $allUsedArea = $em->getRepository(UsedArea::class)->findAll();
        $contractStatusArray = $this->container->getParameter('contracts_status_array');
        foreach ($allUsedArea as $usedArea){
            $contract=$usedArea->getContract();

            if ($this->contractService->isContractActive($em, $contract->getId())){

                $usedArea->setActive(1);

//            /**
//             * @var Contracts $contract
//             */
//            //договора е изтекъл
//            if ($contract->getExpire() < $startOfAgroYear and
//                ($contract->getStatus() != $contractStatusArray['has_annex'] ))
//            {
//                $usedArea->setActive(0);//неактивна площ
//                $contract->setStatus($contractStatusArray['expired']);//изтекъл срок
//            }
//            //има анекс
//            elseif( $contract->getStatus() ==  $contractStatusArray['has_annex'])
//            {
//                $usedArea->setActive(0);//не активна площ
//            }
//            else{
//                $usedArea->setActive(1);//активна площ
//                $contract->setStatus($contractStatusArray['active']);
//            }

            }else{
                $usedArea->setActive(0);
            }
            $em->persist($usedArea);
            $em->persist($contract);
            $em->flush();
        }
    }
}