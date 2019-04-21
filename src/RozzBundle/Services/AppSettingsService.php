<?php
/**
 * Created by PhpStorm.
 * User: Jivko
 * Date: 9.10.2018 г.
 * Time: 18:27
 */

namespace RozzBundle\Services;


use Doctrine\Common\Persistence\ObjectManager;
use RozzBundle\Entity\ApplicationSettings;
use \Doctrine\ORM\Mapping\ClassMetadata;

class AppSettingsService
{
    public function initAppSettings(ObjectManager $em) : ApplicationSettings
    {
        //Задава началото на Агро - Годината от днешна дата
        $appSettingsEntity = new ApplicationSettings();

        $now = new \DateTime('now');
        $agroYearStart = new \DateTime( '1-10-'.date('Y') );
        if ($now < $agroYearStart){
            $agroYearStart = new \DateTime( '1-10-'.(date('Y')-1) );
        }
        $appSettingsEntity -> setAgroYear($agroYearStart);

        //други настройки....


        $em->persist($appSettingsEntity);
        $em->flush();

        return $appSettingsEntity;
    }
}