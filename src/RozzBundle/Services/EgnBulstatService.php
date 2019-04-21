<?php
/**
 * Created by PhpStorm.
 * User: Jivko
 * Date: 18.6.2018 г.
 * Time: 21:00
 */

namespace RozzBundle\Services;


class EgnBulstatService
{
    public function isValidEgn($egn){
//        if ($egn == null){
//            $egn = '';
//        }

        if( mb_strlen($egn) !== 10){
            return false;
        }  else {//Ако стринга има 10 символа

            if (!is_numeric($egn) ){
                return false;
            }else{//Ако символите са числови, умножавам по дадените коефициенти и изчислявам контрол на 10та позиция
                $pos1 = intval(substr($egn,0,1))*2 ;
                $pos2 = intval(substr($egn,1,1))*4;
                $pos3 = intval(substr($egn,2,1))*8;
                $pos4 = intval(substr($egn,3,1))*5;
                $pos5 = intval(substr($egn,4,1))*10;
                $pos6 = intval(substr($egn,5,1))*9;
                $pos7 = intval(substr($egn,6,1))*7;
                $pos8 = intval(substr($egn,7,1))*3;
                $pos9 = intval(substr($egn,8,1))*6;
                $pos10 = intval(substr($egn,9,1));
                $controlPos10 = ($pos1+$pos2+$pos3+$pos4+$pos5+$pos6+$pos7+$pos8+$pos9)%11;

                if ($controlPos10 === $pos10){
                    return true;
                }else{
                    if ( ($controlPos10 === 10) && ($pos10 === 0)){
                        return true;
                    }else{
                        return false;
                    }
                }
            }
        }
    }

    private function Bulstat9Char($Bulstat){
        $pos1 = intval(substr($Bulstat,0,1));
        $pos2 = intval(substr($Bulstat,1,1))*2;
        $pos3 = intval(substr($Bulstat,2,1))*3;
        $pos4 = intval(substr($Bulstat,3,1))*4;
        $pos5 = intval(substr($Bulstat,4,1))*5;
        $pos6 = intval(substr($Bulstat,5,1))*6;
        $pos7 = intval(substr($Bulstat,6,1))*7;
        $pos8 = intval(substr($Bulstat,7,1))*8;
        $pos9 = intval(substr($Bulstat,8,1));
        $controlPos9 = ($pos1+$pos2+$pos3+$pos4+$pos5+$pos6+$pos7+$pos8)%11;

        if ($controlPos9 === $pos9){
            return true;
        }else{
            if ($controlPos9 === 10){
                $pos1 = intval(substr($Bulstat,0,1))*3;
                $pos2 = intval(substr($Bulstat,1,1))*4;
                $pos3 = intval(substr($Bulstat,2,1))*5;
                $pos4 = intval(substr($Bulstat,3,1))*6;
                $pos5 = intval(substr($Bulstat,4,1))*7;
                $pos6 = intval(substr($Bulstat,5,1))*8;
                $pos7 = intval(substr($Bulstat,6,1))*9;
                $pos8 = intval(substr($Bulstat,7,1))*10;
                $pos9 = intval(substr($Bulstat,8,1));
                $controlPos9 = ($pos1+$pos2+$pos3+$pos4+$pos5+$pos6+$pos7+$pos8)%11;

                if ($controlPos9 === $pos9) {
                    return true;
                }else{
                    if ( ($controlPos9 === 10) && ($pos9 === 0)){
                        return true;
                    }else{
                        return false;
                    }
                }
            }else{
                return false;
            }
        }
    }

    private function Bulstat13Char($Bulstat)
    {
        $pos9 = intval(substr($Bulstat,8,1))*2;
        $pos10 = intval(substr($Bulstat,9,1))*7;
        $pos11 = intval(substr($Bulstat,10,1))*3;
        $pos12 = intval(substr($Bulstat,11,1))*5;
        $pos13 = intval(substr($Bulstat,12,1));

        $controlPos13 = ($pos9+$pos10+$pos11+$pos12)%11;
        if ($controlPos13 == $pos13) {
            return true;
        }else{
            if ($controlPos13 == 10){
                $pos9 = intval(substr($Bulstat,8,1))*4;
                $pos10 = intval(substr($Bulstat,9,1))*9;
                $pos11 = intval(substr($Bulstat,10,1))*5;
                $pos12 = intval(substr($Bulstat,11,1))*7;
                $controlPos13 = ($pos9+$pos10+$pos11+$pos12)%11;

                if ($controlPos13 == $pos13) {
                    return true;
                }else{
                    if ( ($controlPos13 == 10) && ($pos13 == 0)){
                        return true;
                    }else{
                        return false;
                    }
                }
            }else//край на if ($controlPos13 == 10)
            {
                return false;
            }
        }
    }

    private function isValidBulstat($Bulstat)
    {
        if ($Bulstat == null){
            $Bulstat = '';
        }
        if(mb_strlen($Bulstat) === 9 || mb_strlen($Bulstat)=== 13){
            if ( !is_numeric($Bulstat) ){
                return false;
            }else {

                if (mb_strlen($Bulstat) === 9) {
                    if ($this->Bulstat9Char($Bulstat)) {
                        return true;
                    } else {
                        return false;
                    }
                }//length === 9
                else {//length === 13
                    if ($this->Bulstat9Char($Bulstat) && $this->Bulstat13Char($Bulstat)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
    }


    public function isValidEgnBulstat($input){
        if ($this->isValidEgn($input) || $this->isValidBulstat($input)){
            return true;
        }else{
            return false;
        }
    }
}