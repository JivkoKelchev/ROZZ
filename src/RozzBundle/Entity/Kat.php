<?php

namespace RozzBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * kat
 *
 * @ORM\Table(name="kat")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\katRepository")
 */
class Kat
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var Lands[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\Lands", mappedBy="kat")
     */
    private $lands;




    public function __construct()
    {
        $this->lands = new ArrayCollection();
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return ArrayCollection|Lands[]
     */
    public function getLands()
    {
        return $this->lands;
    }

    /**
     * @param ArrayCollection|Lands[] $lands
     */
    public function setLands($lands)
    {
        $this->lands = $lands;
    }




}

