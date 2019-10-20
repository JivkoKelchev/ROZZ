<?php

namespace RozzBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsedArea
 *
 * @ORM\Table(name="used_area")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\UsedAreaRepository")
 */
class UsedArea
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
     * @var float
     *
     * @ORM\Column(name="area", type="float")
     */
    private $area;

    /**
     * @var Lands
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Lands", inversedBy="usedArea")
     */
    private $land;

    /**
     * @var Contracts
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Contracts", inversedBy="usedArea")
     */
    private $contract;


    /**
     * @var
     * @ORM\Column(name="price", type="float")
     */
    private $price;


    /**
     * @ORM\Column(name="active", type="integer")
     */
    private $active;

    /**
     * @var string
     *
     * Used only for store neighbour data to be send in twig template.
     * This data is not saved in DB
     */
    private $neighbours;








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
     * @return Lands
     */
    public function getLand(): Lands
    {
        return $this->land;
    }

    /**
     * @param Lands $land
     */
    public function setLand(Lands $land)
    {
        $this->land = $land;
    }

    /**
     * @return Contracts
     */
    public function getContract(): Contracts
    {
        return $this->contract;
    }

    /**
     * @param Contracts $contract
     */
    public function setContract(Contracts $contract)
    {
        $this->contract = $contract;
    }

    /**
     * @return float
     */
    public function getArea(): float
    {
        return $this->area;
    }

    /**
     * @param float $area
     */
    public function setArea(float $area)
    {
        $this->area = $area;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    public function getNeighbours()
    {
        return $this->neighbours;
    }

    public function setNeighbours($neighbours)
    {
        $this->neighbours = $neighbours;
    }

}

