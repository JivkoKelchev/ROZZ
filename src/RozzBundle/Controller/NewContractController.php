<?php

namespace RozzBundle\Controller;

use JMS\SerializerBundle\JMSSerializerBundle;
use RozzBundle\Entity\Examiners;
use RozzBundle\Entity\Holders;
use RozzBundle\Entity\User;
use RozzBundle\Repository\newContractsRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use RozzBundle\Entity\NewContracts;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class NewContractController
 * @package RozzBundle\Controller
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class NewContractController extends Controller
{
    /**
     * @Route("/NewContract", name="NewContract")
     */
    public function newContractIndexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $newContract = $em->getRepository(NewContracts::class)->findBy(['user'=>$this->getUser()]);
        if (!$newContract){
            $newContractEntity = new NewContracts();
            $newContractEntity->setUser($this->getUser());
            $em->persist($newContractEntity);
            $em->flush();
        }
        return $this->render('RozzBundle:NewContract:NewContract.html.twig');
    }

    /**
     * @param Request $request
     * @Route("/newcontract/update", name="new_contract_ajax")
     */
    public function setNewContractProperties(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $newContractEntity = $em->getRepository('RozzBundle:NewContracts')
            ->findOneBy(['user'=>$this->getUser()]);
        if (!$newContractEntity){
            $newContractEntity = new NewContracts();
            $newContractEntity->setUser($this->getUser());
            $em->persist($newContractEntity);
            $em->flush();
        }

        if ($request->isXmlHttpRequest())
        {
            if ($request->getMethod() == 'POST')
            {
                $messageArray = $this->get('new_contract_service')->setNewContract($newContractEntity, $request);
                $messageArrayJson = json_encode($messageArray);
                $em->persist($newContractEntity);
                $em->flush();
                return new JsonResponse($messageArrayJson);
            }
            else
            {
                $serializer = $this->get('json_serializer');


                $newContractEntityJson = $serializer->entity_serialize($newContractEntity);
                dump($newContractEntityJson);
                return new JsonResponse($newContractEntityJson);
            }
        }
        $serializer = $this->get('json_serializer');


        $newContractEntityJson = $serializer->entity_serialize($newContractEntity);
        dump($newContractEntityJson);
        exit;
        return new JsonResponse($newContractEntityJson);
    }//setNewContractProperties


}
