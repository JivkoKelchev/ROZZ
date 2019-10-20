<?php

namespace RozzBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * selectedLand
 *
 * @ORM\Table(name="selected_land")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\selectedLandRepository")
 */
class SelectedLand
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="user", inversedBy="selectedLands")
     */
    private $user;

    /**
     * @var Lands
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Lands", inversedBy="selected")
     */
    private $land;

    /**
     * @var float
     *
     * @ORM\Column(name="area", type="float")
     */
    private $area;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price = 0;

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
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
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
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price)
    {
        $this->price = $price;
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

