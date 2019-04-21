<?php
/**
 * Created by PhpStorm.
 * User: Jivko
 * Date: 28.4.2018 г.
 * Time: 15:36
 */

namespace RozzBundle\Services;


use function PHPSTORM_META\type;
use RozzBundle\Entity\NewContracts;
use Symfony\Component\HttpFoundation\Request;

class NewContractService
{
    public function setNewContract(NewContracts &$newContract, Request $request){
        $messageArray = [];
        if ($request->get('type')){
            $newContractType = $request->get('type');

            $newContract->setType($newContractType);
            if ($newContractType == 1){
                $messageArray['success'] = 'Нов едногодишен договор';
            }elseif ($newContractType == 2){
                $messageArray['success'] = 'Нов многогодишен договор';
            }elseif ($newContractType == 3){
                $messageArray['success'] = 'Нов анекс';
            }

        }
        if ($request->get('application')){
            $newContract->setApplication($request->get('aplication'));
        }

        return $messageArray;
    }


}