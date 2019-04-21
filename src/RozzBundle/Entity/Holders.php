<?php

namespace RozzBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Holders
 *
 * @ORM\Table(name="holders")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\HoldersRepository")
 */
class Holders
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
     * @var string
     *
     * @ORM\Column(name="addres", type="string", length=255)
     */
    private $addres;

    /**
     * @var NewContracts
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\NewContracts", mappedBy="holder")
     *
     */
    private $newContract;

    /**
     * @var Contracts|ArrayCollection
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\Contracts", mappedBy="holder")
     */
    private $contracts;












    public function __construct()
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
     * @return Holders
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
     * @return Holders
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
     * Set addres
     *
     * @param string $addres
     *
     * @return Holders
     */
    public function setAddres($addres)
    {
        $this->addres = $addres;

        return $this;
    }

    /**
     * Get addres
     *
     * @return string
     */
    public function getAddres()
    {
        return $this->addres;
    }

    /**
     * @return NewContracts
     */
    public function getNewContract(): NewContracts
    {
        return $this->newContract;
    }

    /**
     * @param NewContracts $newContract
     */
    public function setNewContract(NewContracts $newContract)
    {
        $this->newContract = $newContract;
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

