<?php

namespace RozzBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Шаблон за договор: редактируемо съдържание (body) + шаблон за ред на имот
 * (row_template), и двата с %токени%. Версиите се пазят, за да могат старите
 * договори да запазят оригиналния си изглед.
 *
 * @ORM\Table(name="contract_template")
 * @ORM\Entity(repositoryClass="RozzBundle\Repository\ContractTemplateRepository")
 */
class ContractTemplate
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * HTML на основния текст на договора с %токени% (вкл. %lands_list%).
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @var string
     * HTML за един имот с %land_*% токени; повтаря се за всеки имот.
     * @ORM\Column(name="row_template", type="text")
     */
    private $rowTemplate;

    /**
     * @var bool
     * Активният шаблон се ползва за НОВИ договори (точно един активен).
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive = false;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getRowTemplate()
    {
        return $this->rowTemplate;
    }

    public function setRowTemplate($rowTemplate)
    {
        $this->rowTemplate = $rowTemplate;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = (bool) $isActive;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
