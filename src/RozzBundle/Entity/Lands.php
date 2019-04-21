<?php

namespace RozzBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * lands
 *
 * @ORM\Table(name="lands")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\landsRepository")
 */
class Lands
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
     * @var string
     *
     * @ORM\Column(name="num", type="string", length=255)
     */
    private $num;

    /**
     * @var float
     *
     * @ORM\Column(name="area", type="float")
     */
    private $area;

    /**
     *@ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @var Doc
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Doc", inversedBy="lands", cascade={"persist"})
     */
    private $doc;

    /**
     * @var Mest
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Mest", inversedBy="lands", cascade={"persist"})
     */
    private $mest;

    /**
     * @var Zem;
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Zem", inversedBy="lands", cascade={"persist"})
     */
    private $zem;

    /**
     * @var Ntp
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Ntp", inversedBy="lands", cascade={"persist"})
     */
    private $ntp;

    /**
     * @var Kat
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Kat", inversedBy="lands", cascade={"persist"})
     */
    private $kat;

    /**
     * @var UsedArea[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\UsedArea", mappedBy="land")
     */
    private $usedArea;

    /**
     * @var SelectedLand
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\SelectedLand", mappedBy="land")
     */
    private $selected = null;

    /**
     *@ORM\OneToOne(targetEntity="RozzBundle\Entity\Comments", mappedBy="land" ,cascade={"persist"})
     */
    private $comments;

    /**
     * Тези полета са стрингови и ще се ползват за създаване на нови
     * землища, местности, нтп и категории
     */

    private $newMest;
    private $newZem;
    private $newNtp;
    private $newKat;
    private $newDoc;






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
     * Set num
     *
     * @param string $num
     *
     * @return lands
     */
    public function setNum($num)
    {
        $this->num = $num;

        return $this;
    }

    /**
     * Get num
     *
     * @return string
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Set area
     *
     * @param float $area
     *
     * @return lands
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return float
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     *
     */
    public function getMest()
    {
        return $this->mest;
    }

    /**
     * @param $mest
     */
    public function setMest( $mest)
    {
        $this->mest = $mest;
    }

    /**
     *
     */
    public function getZem()
    {
        return $this->zem;
    }

    /**
     * @param $zem
     */
    public function setZem($zem)
    {
        $this->zem = $zem;
    }

    /**
     * @return Ntp
     */
    public function getNtp()
    {
        return $this->ntp;
    }

    /**
     * @param $ntp
     */
    public function setNtp($ntp)
    {
        $this->ntp = $ntp;
    }

    /**
     *
     */
    public function getKat()
    {
        return $this->kat;
    }

    /**
     * @param  $kat
     */
    public function setKat($kat)
    {
        $this->kat = $kat;
    }

    /**
     *
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * @param $doc
     */
    public function setDoc($doc)
    {
        $this->doc = $doc;
    }

    /**
     * @return ArrayCollection|UsedArea[]
     */
    public function getUsedArea()
    {
        return $this->usedArea;
    }

    /**
     * @param ArrayCollection|UsedArea[] $usedArea
     */
    public function setUsedArea($usedArea)
    {
        $this->usedArea = $usedArea;
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $coments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return mixed
     */
    public function getNewMest()
    {
        return $this->newMest;
    }

    /**
     * @param mixed $newMest
     */
    public function setNewMest($newMest)
    {
        $this->newMest = $newMest;
    }

    /**
     * @return mixed
     */
    public function getNewZem()
    {
        return $this->newZem;
    }

    /**
     * @param mixed $newZem
     */
    public function setNewZem($newZem)
    {
        $this->newZem = $newZem;
    }

    /**
     * @return mixed
     */
    public function getNewNtp()
    {
        return $this->newNtp;
    }

    /**
     * @param mixed $newNtp
     */
    public function setNewNtp($newNtp)
    {
        $this->newNtp = $newNtp;
    }

    /**
     * @return mixed
     */
    public function getNewKat()
    {
        return $this->newKat;
    }

    /**
     * @param mixed $newKat
     */
    public function setNewKat($newKat)
    {
        $this->newKat = $newKat;
    }

    /**
     * @return mixed
     */
    public function getNewDoc()
    {
        return $this->newDoc;
    }

    /**
     * @param mixed $newDoc
     */
    public function setNewDoc($newDoc)
    {
        $this->newDoc = $newDoc;
    }


}

