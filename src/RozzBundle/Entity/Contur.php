<?php

namespace RozzBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contur
 *
 * @ORM\Table(name="contur")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\ConturRepository")
 */
class Contur
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
     * @var string
     *
     * @ORM\Column(name="Number", type="string", length=255)
     */
    private $number;

    /**
     * @var float
     *
     * @ORM\Column(name="X", type="float")
     */
    private $x;

    /**
     * @var float
     *
     * @ORM\Column(name="Y", type="float")
     */
    private $y;

    /**
     * @var Line[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\Line", mappedBy="contur")
     */
    private $lines;

    /**
     * @var Cad
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Cad")
     */
    private $cad;

    public function __construct()
    {
        $this->lines = new ArrayCollection();
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
     * @return Contur
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
     * @param string $number
     *
     * @return Contur
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set x
     *
     * @param float $x
     *
     * @return Contur
     */
    public function setX($x)
    {
        $this->x = $x;

        return $this;
    }

    /**
     * Get x
     *
     * @return float
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set y
     *
     * @param float $y
     *
     * @return Contur
     */
    public function setY($y)
    {
        $this->y = $y;

        return $this;
    }

    /**
     * Get y
     *
     * @return float
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @return mixed
     */
    public function getLines()
    {
        return $this->lines;
    }

    public function addLine(Line $line)
    {
        $this->lines->add($line);
    }

    /**
     * @param ArrayCollection|Line[] $lines
     */
    public function setLines($lines)
    {
        $this->lines = $lines;
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

