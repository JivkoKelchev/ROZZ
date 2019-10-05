<?php

namespace RozzBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Point
 *
 * @ORM\Table(name="point")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\PointRepository")
 */
class Point
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
     * @var string
     *
     * @ORM\Column(name="Z", type="string", length=255)
     */
    private $z;

    /**
     * @var Line
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Line", inversedBy="points")
     */
    private $line;

    /**
     * @var Cad
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Cad")
     */
    private $cad;


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
     * @return Point
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
     * @return Point
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
     * @return Point
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
     * @return Point
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
     * Set z
     *
     * @param string $z
     *
     * @return Point
     */
    public function setZ($z)
    {
        $this->z = $z;

        return $this;
    }

    /**
     * Get z
     *
     * @return string
     */
    public function getZ()
    {
        return $this->z;
    }

    /**
     * @return mixed
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param Line $line
     */
    public function setLine(Line $line)
    {
        $this->line = $line;
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

