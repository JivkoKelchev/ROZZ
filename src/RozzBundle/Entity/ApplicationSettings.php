<?php

namespace RozzBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApplicationSettings
 *
 * @ORM\Table(name="application_settings")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\ApplicationSettingsRepository")
 */
class ApplicationSettings
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     *
     * @ORM\Column(name="agro_year", type="datetime", nullable=false, unique=false)
     */
    private $agroYear;







    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAgroYear()
    {
        return $this->agroYear;
    }

    /**
     * @param mixed $agroYear
     */
    public function setAgroYear($agroYear)
    {
        $this->agroYear = $agroYear;
    }

}

