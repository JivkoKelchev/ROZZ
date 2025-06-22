<?php
/**
 * Created by PhpStorm.
 * User: Jivko
 * Date: 6.10.2018 г.
 * Time: 21:27
 */

namespace RozzBundle\Services;


use Doctrine\Common\Persistence\ObjectManager;
use RozzBundle\Entity\Lands;
use RozzBundle\Entity\UsedArea;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    public function setUsedAreaForActiveContracts(ObjectManager $em, \DateTime $startOfAgroYear) {
        // Clone instead of creating a new DateTime
        $endOfAgroYear = clone $startOfAgroYear;
        $endOfAgroYear->add(new \DateInterval('P1Y'))->sub(new \DateInterval('P1D'));

        // Process in batches
        $batchSize = 100;
        $offset = 0;

        do {
            // Get only a batch of entities at a time
            $query = $em->createQuery('
                SELECT ua, c 
                FROM ' . UsedArea::class . ' ua
                JOIN ua.contract c
                WHERE ua.active = 1
            ')
            ->setMaxResults($batchSize)
            ->setFirstResult($offset);

            $usedAreas = $query->getResult();
            $count = count($usedAreas);

            if ($count > 0) {
                foreach ($usedAreas as $usedArea) {
                    $contract = $usedArea->getContract();

                    $shouldBeActive = $contract->getStart() <= $startOfAgroYear &&
                                      $contract->getExpire() >= $endOfAgroYear &&
                                      $contract->getStatus() != 2;

                    if (!$shouldBeActive) {
                        $usedArea->setActive(0);
                    }
                    // Only persist if it changed
                }

                $em->flush();
                $em->clear(); // Clear memory after each batch

                $offset += $count;
            }
        } while ($count > 0);

        return $offset; // Return total processed
    }
}