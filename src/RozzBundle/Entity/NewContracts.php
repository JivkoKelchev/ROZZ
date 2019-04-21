<?php

namespace RozzBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * contracts
 *
 * @ORM\Table(name="new_contracts")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\newContractsRepository")
 */
class NewContracts
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
     * @var  int
     * @ORM\Column(name="type", nullable=true)
     * values:
     * null -one year
     * 1-one year contract
     * 2-multy years contracts
     * 3- annex
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="application", type="string", length=255, nullable=true, unique=false)
     */
    private $application;

    /**
     * @var string
     *
     * @ORM\Column(name="resheniq", type="string", length=255, nullable=true, unique=false)
     */
    private $resheniq;

    /**
     *
     *
     * @ORM\Column(name="expire", type="datetime", nullable=true, unique=false)
     */
    private $expire;

    /**
     * @ORM\Column(name="start", type="datetime", nullable=true, unique=false)
     */
    private $start;

    /**
     * Брой години ще се използва за изчислявване на expire
     *
     * полето се използва само за рендериране на форма
     */
    private $years;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="RozzBundle\Entity\User", inversedBy="newContract")
     */
    private $user;

    /**
     * @var Examiners
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Examiners", inversedBy="newContract")
     */
    private $examiner;

    /**
     * @var Holders
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Holders", inversedBy="newContract")
     */
    private $holder;

    /**
     * @ORM\Column(name="neighbours", type="array", nullable=true, unique=false)
     */
    private $neighbours;

    /**
     * @var int
     *
     * @ORM\Column(name="contract_id_for_annex_only", type="integer", nullable=true, unique=false)
     */
    private $annexContractId;

    /**
     * @var int
     *
     * @ORM\Column(name="contract_id_for_edit", type="integer", nullable=true, unique=false)
     */
    private $editContractId;

    /**
     * @var Mayors
     * Не се ползва в базата,
     * използва се за съвместимост на contract_preview и contract_view
     * попълва се само с настоящия кмет в контролера на превюто
     */
    private $mayor;



    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param string $application
     */
    public function setApplication(string $application)
    {
        $this->application = $application;
    }

    /**
     * @return string
     */
    public function getResheniq()
    {
        return $this->resheniq;
    }

    /**
     * @param string $resheniq
     */
    public function setResheniq(string $resheniq)
    {
        $this->resheniq = $resheniq;
    }

    /**
     *
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     *
     */
    public function setExpire( $expire)
    {
        $this->expire = $expire;
    }

    /**
     * @return User
     */
    public function getUser()
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
     * @return mixed
     */
    public function getNeighbours()
    {
        return $this->neighbours;
    }

    /**
     * @return Examiners
     */
    public function getExaminer()
    {
        return $this->examiner;
    }

    /**
     * @param Examiners $examiner
     */
    public function setExaminer(Examiners $examiner)
    {
        $this->examiner = $examiner;
    }

    /**
     * @return Holders
     */
    public function getHolder()
    {
        return $this->holder;
    }

    /**
     * @param Holders $holder
     */
    public function setHolder(Holders $holder)
    {
        $this->holder = $holder;
    }

    /**
     *
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     */
    public function setType( $type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }


    /**
     * @param mixed $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @param mixed $neighbours
     */
    public function setNeighbours($neighbours)
    {
        $this->neighbours = $neighbours;
    }

    /**
     * @return int
     */
    public function getAnnexContractId()
    {
        return $this->annexContractId;
    }

    /**
     * @param int $anexContractId
     */
    public function setAnnexContractId(int $annexContractId)
    {
        $this->annexContractId = $annexContractId;
    }

    //Използвам гo за съвмвстимост на Contract и NewContract обекти във Вю-то
    public function getNum()
    {
        return null;
    }
    //Използвам гo за съвмвстимост на Contract и NewContract обекти във Вю-то
    public function getDocFile()
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getYears()
    {
        return $this->years;
    }

    /**
     * @param Int $years
     */
    public function setYears(Int $years )
    {
        $this->years = $years;
    }

    /**
     * @return Mayors
     */
    public function getMayor(): Mayors
    {
        return $this->mayor;
    }

    /**
     * @param Mayors $mayor
     */
    public function setMayor(Mayors $mayor)
    {
        $this->mayor = $mayor;
    }


    public function getEditContractId()
    {
        return $this->editContractId;
    }

    /**
     * @param int $editContractId
     */
    public function setEditContractId( $editContractId)
    {
        $this->editContractId = $editContractId;
    }

}