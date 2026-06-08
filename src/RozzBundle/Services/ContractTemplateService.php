<?php

namespace RozzBundle\Services;

use Doctrine\ORM\EntityManager;
use RozzBundle\Entity\ContractTemplate;
use RozzBundle\Entity\Contracts;

/**
 * Управление на шаблоните за договори: лениво създаване на наследения (legacy)
 * шаблон, намиране на активния шаблон и на шаблона за конкретен договор.
 */
class ContractTemplateService
{
    const LEGACY_NAME = 'Стандартен договор (наследен)';

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Създава наследения шаблон при първо извикване (от текущия HTML изглед) и
     * го маркира като активен. Идемпотентно — ако вече има шаблони, връща legacy.
     *
     * @return ContractTemplate
     */
    public function ensureLegacyTemplate()
    {
        $legacy = $this->getLegacyTemplate();
        if ($legacy !== null) {
            return $legacy;
        }

        $dir = dirname(__DIR__) . '/Resources/contract_templates/';
        $body = file_get_contents($dir . 'legacy_body.html');
        $row = file_get_contents($dir . 'legacy_row.html');

        $legacy = new ContractTemplate();
        $legacy->setName(self::LEGACY_NAME);
        $legacy->setBody($body);
        $legacy->setRowTemplate($row);
        $legacy->setIsActive(true);
        $legacy->setCreatedAt(new \DateTime());

        $this->em->persist($legacy);
        $this->em->flush();

        return $legacy;
    }

    /**
     * Презарежда съдържанието на наследения шаблон от файловете в bundle-а.
     * Използва се при промяна на наследения текст/токени (изходът остава същият).
     *
     * @return ContractTemplate
     */
    public function syncLegacyFromFiles()
    {
        $legacy = $this->ensureLegacyTemplate();

        $dir = dirname(__DIR__) . '/Resources/contract_templates/';
        $legacy->setBody(file_get_contents($dir . 'legacy_body.html'));
        $legacy->setRowTemplate(file_get_contents($dir . 'legacy_row.html'));
        $this->em->flush();

        return $legacy;
    }

    /**
     * Наследеният шаблон е най-старият (първо създаден) запис.
     *
     * @return ContractTemplate|null
     */
    public function getLegacyTemplate()
    {
        $templates = $this->em->getRepository(ContractTemplate::class)
            ->findBy([], ['id' => 'ASC'], 1);

        return $templates ? $templates[0] : null;
    }

    /**
     * Активният шаблон се ползва за нови договори. Ако няма такъв, наследеният.
     *
     * @return ContractTemplate
     */
    public function getActiveTemplate()
    {
        $this->ensureLegacyTemplate();

        $active = $this->em->getRepository(ContractTemplate::class)
            ->findOneBy(['isActive' => true]);

        return $active ?: $this->getLegacyTemplate();
    }

    /**
     * Шаблонът, с който да се рендира даден договор: неговият, иначе наследеният.
     *
     * @param Contracts $contract
     * @return ContractTemplate
     */
    public function getTemplateForContract(Contracts $contract)
    {
        $this->ensureLegacyTemplate();

        return $contract->getTemplate() ?: $this->getLegacyTemplate();
    }

    /**
     * Всички шаблони, най-новите най-отгоре.
     *
     * @return ContractTemplate[]
     */
    public function listAll()
    {
        $this->ensureLegacyTemplate();

        return $this->em->getRepository(ContractTemplate::class)
            ->findBy([], ['id' => 'DESC']);
    }

    /**
     * Брой договори, които се рендират с този шаблон. За наследения шаблон се
     * включват и старите договори без зададен шаблон (template_id NULL), защото
     * те се рендират с него. Използва се, за да решим дали редакцията да създаде
     * нова версия (за да не променим вече създадени договори).
     *
     * @param ContractTemplate $template
     * @return int
     */
    public function countContractsUsing(ContractTemplate $template)
    {
        $count = $this->countDirectlyBound($template);

        $legacy = $this->getLegacyTemplate();
        if ($legacy !== null && $legacy->getId() === $template->getId()) {
            $count += $this->countWithoutTemplate();
        }

        return $count;
    }

    /**
     * Брой договори с пряка връзка към шаблона (за проверка при изтриване).
     *
     * @param ContractTemplate $template
     * @return int
     */
    public function countDirectlyBound(ContractTemplate $template)
    {
        $dql = 'SELECT COUNT(c.id) FROM ' . Contracts::class . ' c WHERE c.template = :t';

        return (int) $this->em->createQuery($dql)
            ->setParameter('t', $template)
            ->getSingleScalarResult();
    }

    /**
     * Брой стари договори без зададен шаблон (рендират се с наследения).
     *
     * @return int
     */
    private function countWithoutTemplate()
    {
        $dql = 'SELECT COUNT(c.id) FROM ' . Contracts::class . ' c WHERE c.template IS NULL';

        return (int) $this->em->createQuery($dql)->getSingleScalarResult();
    }

    /**
     * Прави подадения шаблон активен (и деактивира всички останали).
     *
     * @param ContractTemplate $template
     */
    public function activate(ContractTemplate $template)
    {
        $all = $this->em->getRepository(ContractTemplate::class)->findAll();
        foreach ($all as $t) {
            $t->setIsActive($t->getId() === $template->getId());
        }
        $this->em->flush();
    }

    public function isLegacy(ContractTemplate $template)
    {
        $legacy = $this->getLegacyTemplate();

        return $legacy !== null && $legacy->getId() === $template->getId();
    }
}
