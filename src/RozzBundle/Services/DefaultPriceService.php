<?php

namespace RozzBundle\Services;

use Doctrine\ORM\EntityManager;
use RozzBundle\Entity\Ntp;
use RozzBundle\Entity\NtpZemPrice;
use RozzBundle\Entity\Zem;

/**
 * Стандартни цени по НТП и землище (Zem): търсене при създаване на договор и
 * запис на матрицата от администраторския екран.
 */
class DefaultPriceService
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Връща стандартната цена за дадени НТП и землище или null, ако няма зададена.
     *
     * @param Ntp $ntp
     * @param Zem $zem
     * @return float|null
     */
    public function getPriceFor(Ntp $ntp = null, Zem $zem = null)
    {
        if ($ntp === null || $zem === null) {
            return null;
        }

        $entity = $this->em->getRepository(NtpZemPrice::class)
            ->findOneBy(['ntp' => $ntp, 'zem' => $zem]);

        return $entity ? $entity->getPrice() : null;
    }

    /**
     * Връща всички цени за дадено землище, индексирани по ntp_id.
     *
     * @param Zem $zem
     * @return array  [ntp_id => price]
     */
    public function getPricesForZem(Zem $zem)
    {
        $rows = $this->em->getRepository(NtpZemPrice::class)->findBy(['zem' => $zem]);
        $map = [];
        foreach ($rows as $row) {
            $map[$row->getNtp()->getId()] = $row->getPrice();
        }

        return $map;
    }

    /**
     * Записва матрицата с цени за дадено землище. За всеки НТП:
     *  - празна стойност изтрива съществуващата цена,
     *  - число създава/обновява цената.
     *
     * @param Zem   $zem
     * @param array $pricesByNtpId  [ntp_id => price|'' ]
     */
    public function saveMatrix(Zem $zem, array $pricesByNtpId)
    {
        $repo = $this->em->getRepository(NtpZemPrice::class);

        foreach ($pricesByNtpId as $ntpId => $rawPrice) {
            $ntp = $this->em->getRepository(Ntp::class)->find($ntpId);
            if ($ntp === null) {
                continue;
            }

            $existing = $repo->findOneBy(['ntp' => $ntp, 'zem' => $zem]);
            $isEmpty = ($rawPrice === null || trim((string) $rawPrice) === '');

            if ($isEmpty) {
                if ($existing !== null) {
                    $this->em->remove($existing);
                }
                continue;
            }

            $price = (float) str_replace(',', '.', $rawPrice);
            if ($existing === null) {
                $existing = new NtpZemPrice();
                $existing->setNtp($ntp);
                $existing->setZem($zem);
                $this->em->persist($existing);
            }
            $existing->setPrice($price);
        }

        $this->em->flush();
    }
}
