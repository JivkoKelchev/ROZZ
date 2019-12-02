<?php

namespace RozzBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

const TYPE_CONTRACT = 1;
const TYPE_MULTI_YEAR_CONTRACT = 2;
const TYPE_ANNEX = 3;
const TYPE_EDIT = 4;

const STATUS_CONTRACT = 1;
const STATUS_HAS_ANNEX = 2;
const STATUS_EXPIRED = 3;
const STATUS_TERMINATED = 4;
/**
 * contracts
 *
 * @ORM\Table(name="contracts")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\contractsRepository")
 */
class Contracts
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
     * @ORM\Column(name="num", type="string", nullable=true)
     */
    private $num;

    /**
     * @var  int
     * @ORM\Column(name="type", nullable=true, type="integer")
     * values:
     * null -one year
     * 1-contract
     * 2-contract
     * 3- annex
     *
     */
    private $type;


    /**
     * @var  int
     * @ORM\Column(name="status", nullable=true, type="integer")
     * values:
     * null -active
     * 1-active
     * 2-has_annex
     * 3- expired
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="docFile", type="string", length=255, nullable=true)
     */
    private $docFile;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="string", length=255, nullable=true)
     */
    private $reason;

    /**
     * @var string
     *
     * @ORM\Column(name="application", type="string", length=255)
     */
    private $application;

    /**
     * @var string
     *
     * @ORM\Column(name="resheniq", type="string", length=255)
     */
    private $resheniq;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(name="start", type="datetime", nullable=true, unique=false)
     */
    private $start;

    /**
     * @var string
     *
     * @ORM\Column(name="expire", type="datetime")
     */
    private $expire;

    /**
     * @var UsedArea[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="RozzBundle\Entity\UsedArea", mappedBy="contract")
     */
    private $usedArea;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\User", inversedBy="contracts")
     */
    private $user;

    /**
     * todo to be removed after migration!
     * use examiners!!!
     * @var Examiners
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Examiners", inversedBy="contracts")
     */
    private $examiner;

    /**
     * Many Contracts have Many Examiners.
     * @ORM\ManyToMany(targetEntity="RozzBundle\Entity\Examiners", inversedBy="contractsMany")
     * @ORM\JoinTable(name="contracts_examiners")
     */
    private $examiners;

    /**
     * @var Holders
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Holders", inversedBy="contracts")
     */
    private $holder;

    /**
     * @var Mayors
     * @ORM\ManyToOne(targetEntity="RozzBundle\Entity\Mayors", inversedBy="contracts")
     */
    private $mayor;

    /**
     * @ORM\Column(name="neighbours", type="array", nullable=true, unique=false)
     */
    private $neighbours;

    /**
     * @var int
     *
     * @ORM\Column(name="annex_contract_id", type="integer", nullable=true)
     */
    private $annexContractId;







    public function __construct()
    {
        $this->examiners = new ArrayCollection();
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
     * Set docFile
     *
     * @param string $docFile
     *
     * @return contracts
     */
    public function setDocFile($docFile)
    {
        $this->docFile = $docFile;

        return $this;
    }

    /**
     * Get docFile
     *
     * @return string
     */
    public function getDocFile()
    {
        return $this->docFile;
    }

    /**
     * Set application
     *
     * @param string $application
     *
     * @return contracts
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get application
     *
     * @return string
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set resheniq
     *
     * @param string $resheniq
     *
     * @return contracts
     */
    public function setResheniq($resheniq)
    {
        $this->resheniq = $resheniq;

        return $this;
    }

    /**
     * Get resheniq
     *
     * @return string
     */
    public function getResheniq()
    {
        return $this->resheniq;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return contracts
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set expire
     *
     * @param string $expire
     *
     * @return contracts
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;

        return $this;
    }

    /**
     * Get expire
     *
     * @return string
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @return ArrayCollection|UsedArea[]
     */
    public function getUsedArea()
    {
        return $this->usedArea;
    }

    /**
     * @param ArrayCollection|UsedArea[] $usedArea
     */
    public function setUsedArea($usedArea)
    {
        $this->usedArea = $usedArea;
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
    public function getHolder(): Holders
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

    /**
     * @return string
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * @param string $num
     */
    public function setNum(string $num)
    {
        $this->num = $num;
    }


    public function getType()
    {
        return $this->type;
    }


    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getNeighbours($realNum = false)
    {
        if($realNum){
            return $this->neighbours;
        }
        $neighbours = $this->neighbours;
        //rename neighbours keys because form builder don't like '.' in name
        $landNumbers = array_keys($neighbours);
        $newNeighbours = [];
        foreach ($landNumbers as $number) {
            $newNumber = str_replace('.', '_', $number);
            $newNeighbours[$newNumber] = $neighbours[$number];
        }
        return $newNeighbours;
    }

    /**
     * @param mixed $neighbours
     */
    public function setNeighbours($neighbours)
    {
        $this->neighbours = [];
        foreach ($neighbours as $landNum => $neighbour)
        {
            $number = str_replace('_', '.', $landNum);
            $this->neighbours[$number] = $neighbour;
        }
    }

    

    /**
     * @return int
     */
    public function getAnnexContractId(): int
    {
        return $this->annexContractId;
    }

    /**
     * @param int $annexContractId
     */
    public function setAnnexContractId(int $annexContractId)
    {
        $this->annexContractId = $annexContractId;
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
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param mixed $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * @return mixed
     */
    public function getExaminers()
    {
        return $this->examiners;
    }

    /**
     * @param mixed $examiners
     */
    public function setExaminers($examiners)
    {
        $this->examiners = $examiners;
    }

    public function addExaminer(Examiners $examiner)
    {
        $this->examiners->add($examiner);
    }

}

