<?php

namespace RozzBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Examiners
 *
 * @ORM\Table(name="examiners")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\ExaminersRepository")
 */
class Examiners
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
     * @ORM\Column(name="position", type="string", length=255)
     */
    private $position;

    /**
     * @var Contracts|ArrayCollection
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\Contracts", mappedBy="examiner")
     */
    private $contracts;

    /**
     * @var NewContracts
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\NewContracts", mappedBy="examiner")
     *
     */
    private $newContract;







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
     * @return Examiners
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
     * Set position
     *
     * @param string $position
     *
     * @return Examiners
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
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


    public function getNewContract()
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


}

