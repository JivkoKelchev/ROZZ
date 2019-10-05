<?php

namespace RozzBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Line
 *
 * @ORM\Table(name="line")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\LineRepository")
 */
class Line
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
     * @var int
     *
     * @ORM\Column(name="Type", type="integer")
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="Number", type="integer")
     */
    private $number;

    /**
     * @var int
     *
     * @ORM\Column(name="BorderType", type="integer")
     */
    private $borderType;

    /**
     * @var Point[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\Point", mappedBy="line")
     */
    private $points;

    /**
     * @var Contur
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Contur", inversedBy="lines")
     */
    private $contur;

    /**
     * @var Cad
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Cad")
     */
    private $cad;

    public function __construct()
    {
        $this->points = new ArrayCollection();
    }


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
     * Set type
     *
     * @param integer $type
     *
     * @return Line
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set number
     *
     * @param integer $number
     *
     * @return Line
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set borderType
     *
     * @param integer $borderType
     *
     * @return Line
     */
    public function setBorderType($borderType)
    {
        $this->borderType = $borderType;

        return $this;
    }

    /**
     * Get borderType
     *
     * @return int
     */
    public function getBorderType()
    {
        return $this->borderType;
    }

    /**
     * @return mixed
     */
    public function getPoints()
    {
        return $this->points;
    }

    public function addPoint(Point $point)
    {
        $this->points->add($point);
    }

    /**
     * @param ArrayCollection|Point[] $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }

    /**
     * @return Contur
     */
    public function getContur(): Contur
    {
        return $this->contur;
    }

    /**
     * @param Contur $contur
     */
    public function setContur(Contur $contur)
    {
        $this->contur = $contur;
    }

    /**
     * @return Cad
     */
    public function getCad(): Cad
    {
        return $this->cad;
    }

    /**
     * @param Cad $cad
     */
    public function setCad(Cad $cad)
    {
        $this->cad = $cad;
    }


}

