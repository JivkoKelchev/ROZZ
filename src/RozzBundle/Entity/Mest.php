<?php

namespace RozzBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mest
 *
 * @ORM\Table(name="mest")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\MestRepository")
 */
class Mest
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
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\Lands", mappedBy="mest")
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
     * Set name
     *
     * @param string $name
     *
     * @return Mest
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

