<?php

namespace RozzBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cad
 *
 * @ORM\Table(name="cad")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\CadRepository")
 */
class Cad
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
     * @ORM\Column(name="Version", type="string", length=255)
     */
    private $version;

    /**
     * @var int
     *
     * @ORM\Column(name="Ekatte", type="integer")
     */
    private $ekatte;

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="Window", type="string", length=255)
     */
    private $window;

    /**
     * @var string
     *
     * @ORM\Column(name="Reference", type="string", length=255)
     */
    private $reference;


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
     * Set version
     *
     * @param string $version
     *
     * @return Cad
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set ekatte
     *
     * @param integer $ekatte
     *
     * @return Cad
     */
    public function setEkatte($ekatte)
    {
        $this->ekatte = $ekatte;

        return $this;
    }

    /**
     * Get ekatte
     *
     * @return int
     */
    public function getEkatte()
    {
        return $this->ekatte;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Cad
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
     * Set window
     *
     * @param string $window
     *
     * @return Cad
     */
    public function setWindow($window)
    {
        $this->window = $window;

        return $this;
    }

    /**
     * Get window
     *
     * @return string
     */
    public function getWindow()
    {
        return $this->window;
    }

    /**
     * Set reference
     *
     * @param string $reference
     *
     * @return Cad
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }
}

