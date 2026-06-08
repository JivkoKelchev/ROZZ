<?php

namespace RozzBundle\Services;

use RozzBundle\Entity\Contracts;
use RozzBundle\Entity\ContractTemplate;

/**
 * Рендира договор от шаблон (ContractTemplate) чрез заместване на %токени%.
 *
 * Работи еднакво за запазен договор (Contracts + UsedArea[]) и за чернова
 * (NewContracts + SelectedLand[]) — и двата излагат getLand/getArea/getPrice/
 * getNeighbours и съответните getX за основните данни.
 */
class ContractTemplateRenderer
{
    /**
     * @var PriceToWordsService
     */
    private $priceToWords;

    public function __construct(PriceToWordsService $priceToWords)
    {
        $this->priceToWords = $priceToWords;
    }

    /**
     * @param ContractTemplate $template
     * @param Contracts|object $data   договор или чернова
     * @param iterable         $lands  UsedArea[] или SelectedLand[]
     * @return string HTML
     */
    public function render(ContractTemplate $template, $data, $lands)
    {
        $tokens = $this->buildTokens($template, $data, $lands);

        $body = $template->getBody();
        foreach ($tokens as $key => $value) {
            $body = str_replace('[' . $key . ']', $value, $body);
        }

        return $body;
    }

    /**
     * Сглобява всички [токени] за основния текст.
     */
    private function buildTokens(ContractTemplate $template, $data, $lands)
    {
        $currency = $data->getCurrency();
        $isBgn = ($currency === Contracts::CURRENCY_BGN);

        //Суми по имоти
        $totalArea = 0.0;
        $totalPrice = 0.0;
        foreach ($lands as $land) {
            $totalArea += $land->getArea();
            $totalPrice += $land->getPrice() * $land->getArea();
        }

        //Списък с имоти (от шаблона за ред)
        $landsList = $this->renderLandsList($template->getRowTemplate(), $lands, $isBgn);

        //Срок и брой екземпляри зависят от годините между начало и край
        $years = $this->intervalYears($data);
        if ($years > 1) {
            $termDisplay = ($years + 1) . ' години';
            $copiesClause = 'Настоящият договор се сключи в 5/пет/ еднообразни екземпляра – по '
                . 'един за всяка от странитe, един за Деловодство при Община Велинград и два за Служба по вписванията при РС Велинград.';
        } else {
            $termDisplay = 'една година';
            $copiesClause = 'Настоящият договор се сключи в 3/три/ еднообразни екземпляра – по '
                . 'един за всяка от странитe и един за Деловодство при Община Велинград.';
        }

        $landsCount = $this->countItems($lands);
        $landsObjectPhrase = $landsCount > 1
            ? 'посочените по-горе имоти'
            : 'посочения по-горе имот';

        //Цена за целия период на договора = годишна цена * брой години
        $periodYears = $this->periodYears($data);
        $wholePeriodPrice = $totalPrice * $periodYears;

        //Скаларните стойности се екранират като в Twig ({{ }}); съставните
        //HTML токени (lands_list, examiners_block) се вмъкват сурово.
        return [
            'основание'         => $this->e($data->getReason()),
            'решение'           => $this->e($data->getResheniq()),
            'заявление'         => $this->e($data->getApplication()),
            'кмет'              => $this->e($data->getMayor()->getName()),
            'наемател'          => $this->e($data->getHolder()->getName()),
            'егн'               => $this->e($data->getHolder()->getEGN()),
            'адрес'             => $this->e($data->getHolder()->getAddres()),
            'списък_имоти'      => $landsList,
            'обща_площ'         => $this->e($this->num($totalArea)),
            'обща_цена'         => $this->e(number_format($totalPrice, 2, '.', ',')),
            'цена_словом'       => $this->e($this->priceToWords->toWords($totalPrice, $currency)),
            'цена за целият период' => $this->e(number_format($wholePeriodPrice, 2, '.', ',')),
            'цена за целият период словом' => $this->e($this->priceToWords->toWords($wholePeriodPrice, $currency)),
            'валута'            => $this->e($isBgn ? 'лева' : 'евро'),
            'начална_дата'      => $this->e($data->getStart() ? $data->getStart()->format('d.m.y') : ''),
            'крайна_дата'       => $this->e($data->getExpire() ? $data->getExpire()->format('d.m.y') : ''),
            'срок'              => $this->e($termDisplay),
            'имот_имоти'        => $this->e($landsObjectPhrase),
            'екземпляри'        => $this->e($copiesClause),
            'изготвил'          => $this->e($data->getUser()->getName()),
            'длъжност_изготвил' => $this->e($data->getUser()->getPosition()),
            'съгласували'       => $this->renderExaminers($data),
        ];
    }

    /**
     * Повтаря шаблона за ред за всеки имот и ги съединява.
     */
    private function renderLandsList($rowTemplate, $lands, $isBgn)
    {
        $unit = $isBgn ? 'лв./дка' : 'евро/дка';
        $rows = [];
        $index = 1;
        foreach ($lands as $land) {
            //„Имот“ ако се ползва цялата площ, иначе „Част от имот“
            $usedArea = (float) $land->getArea();
            $totalLandArea = (float) $land->getLand()->getArea();
            $partLabel = ($usedArea + 0.0001 < $totalLandArea) ? 'Част от имот' : 'Имот';

            $map = [
                'пореден_номер'      => $this->e($index),
                'имот_идентификатор' => $this->e($land->getLand()->getNum()),
                'местност'           => $this->e($land->getLand()->getMest()->getName()),
                'землище'            => $this->e($land->getLand()->getZem()->getName()),
                'нтп'                => $this->e($land->getLand()->getNtp()->getName()),
                'категория'          => $this->e($land->getLand()->getKat()->getName()),
                'площ'               => $this->e($this->num($land->getArea())),
                'цена_дка'           => $this->e($this->num($land->getPrice())),
                'мерна_единица'      => $unit,
                'съседи'             => $this->e($land->getNeighbours() !== null ? $land->getNeighbours() : ''),
                'Част от имот'       => $this->e($partLabel),
            ];
            $row = $rowTemplate;
            foreach ($map as $key => $value) {
                $row = str_replace('[' . $key . ']', $value, $row);
            }
            $rows[] = $row;
            $index++;
        }

        return implode("\n", $rows);
    }

    /**
     * Блок "Съгласувал" за всеки проверяващ.
     */
    private function renderExaminers($data)
    {
        $examiners = $data->getExaminers();
        $count = $this->countItems($examiners);
        if ($count === 0) {
            return '';
        }

        //Едно заглавие: единствено число за един, множествено за повече
        $heading = $count > 1 ? 'Съгласували :' : 'Съгласувал :';
        $html = '    <p>' . $heading . "</p>\n";

        foreach ($examiners as $examiner) {
            $html .= '    <p class="placeholder">' . "\n"
                . '        ' . $this->e($examiner->getName()) . "<br/>\n"
                . '        ' . $this->e($examiner->getPosition()) . "\n"
                . "    </p>\n";
        }

        return $html;
    }

    /**
     * Брой години между начало и край (както в стария изглед:
     * data.expire.diff(data.start) -> y). Винаги положителна стойност.
     */
    private function intervalYears($data)
    {
        $start = $data->getStart();
        $expire = $data->getExpire();
        if (!$start || !$expire) {
            return 0;
        }

        return (int) $expire->diff($start)->y;
    }

    /**
     * Брой години на договора (закръглено нагоре). Договорите се създават с
     * край = начало + N години - 1 ден, затова закръгляме нагоре, за да получим N.
     */
    private function periodYears($data)
    {
        $start = $data->getStart();
        $expire = $data->getExpire();
        if (!$start || !$expire) {
            return 1;
        }
        $diff = $start->diff($expire);
        $years = $diff->y + (($diff->m > 0 || $diff->d > 0) ? 1 : 0);

        return max(1, $years);
    }

    private function countItems($lands)
    {
        if (is_array($lands)) {
            return count($lands);
        }
        if ($lands instanceof \Countable) {
            return count($lands);
        }
        $n = 0;
        foreach ($lands as $ignored) {
            $n++;
        }

        return $n;
    }

    /**
     * Извежда число както Twig ({{ value }}) — без излишни нули.
     */
    private function num($value)
    {
        return (string) (0 + $value);
    }

    /**
     * HTML-екраниране като автоекранирането на Twig ({{ }}).
     */
    private function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
