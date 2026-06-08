<?php

namespace RozzBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Стандартна цена за дка по НТП и землище (Zem), задавана от администратор.
 * Една цена на двойка (НТП, землище).
 *
 * @ORM\Table(name="ntp_zem_price", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_ntp_zem", columns={"ntp_id", "zem_id"})})
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\NtpZemPriceRepository")
 */
class NtpZemPrice
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
     * @var Ntp
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Ntp")
     * @ORM\JoinColumn(name="ntp_id", referencedColumnName="id", nullable=false)
     */
    private $ntp;

    /**
     * @var Zem
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Zem")
     * @ORM\JoinColumn(name="zem_id", referencedColumnName="id", nullable=false)
     */
    private $zem;

    /**
     * @var float
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    public function getId()
    {
        return $this->id;
    }

    public function getNtp()
    {
        return $this->ntp;
    }

    public function setNtp(Ntp $ntp)
    {
        $this->ntp = $ntp;
    }

    public function getZem()
    {
        return $this->zem;
    }

    public function setZem(Zem $zem)
    {
        $this->zem = $zem;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }
}
