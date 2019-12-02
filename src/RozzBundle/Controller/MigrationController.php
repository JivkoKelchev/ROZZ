<?php

namespace RozzBundle\Controller;

use RozzBundle\Entity\Contracts;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MigrationController extends Controller
{
    /**
     * @param $name
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("migrate", name="migrate")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $contracts = $em->getRepository(Contracts::class)->findAll();

        foreach ($contracts as $contract){
            $currnetExaminer = $contract->getExaminer();

            if($currnetExaminer){
                $contract->addExaminer($currnetExaminer);
            }

            $em->persist($currnetExaminer);
            $em->flush();
        }

        return new JsonResponse('migration success');
    }
}
