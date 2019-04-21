<?php

namespace RozzBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mayors
 *
 * @ORM\Table(name="mayors")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\MayorsRepository")
 */
class Mayors
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="EGN", type="string", length=255, unique=true)
     */
    private $eGN;

    /**
     * @var bool
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status;

    /**
     * @var ArrayCollection|Contracts
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\Contracts", mappedBy="mayor")
     */
    private $contracts;






    public  function __construct()
    {
        $this->contracts = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Mayors
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set eGN
     *
     * @param string $eGN
     *
     * @return Mayors
     */
    public function setEGN($eGN)
    {
        $this->eGN = $eGN;

        return $this;
    }

    /**
     * Get eGN
     *
     * @return string
     */
    public function getEGN()
    {
        return $this->eGN;
    }

    /**
     * Set status
     *
     * @param boolean $status
     *
     * @return Mayors
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return ArrayCollection|Contracts
     */
    public function getContracts()
    {
        return $this->contracts;
    }

    /**
     * @param ArrayCollection|Contracts $contracts
     */
    public function setContracts($contracts)
    {
        $this->contracts = $contracts;
    }


}

