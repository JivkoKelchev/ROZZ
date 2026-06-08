<?php

namespace RozzBundle\Services;

use RozzBundle\Entity\Contracts;

/**
 * Изписва парична сума словом на български език, съобразено с валутата и рода.
 *
 * Примери:
 *   53.62 BGN -> "петдесет и три лева и шестдесет и две стотинки"
 *   53.62 EUR -> "петдесет и три евро и шестдесет и два цента"
 *
 * Поддържа цели стойности до 999 999 (повече от достатъчно за договорите).
 */
class PriceToWordsService
{
    private static $hundreds = [
        1 => 'сто', 2 => 'двеста', 3 => 'триста', 4 => 'четиристотин',
        5 => 'петстотин', 6 => 'шестстотин', 7 => 'седемстотин',
        8 => 'осемстотин', 9 => 'деветстотин',
    ];

    private static $tens = [
        2 => 'двадесет', 3 => 'тридесет', 4 => 'четиридесет', 5 => 'петдесет',
        6 => 'шестдесет', 7 => 'седемдесет', 8 => 'осемдесет', 9 => 'деветдесет',
    ];

    private static $teens = [
        10 => 'десет', 11 => 'единадесет', 12 => 'дванадесет', 13 => 'тринадесет',
        14 => 'четиринадесет', 15 => 'петнадесет', 16 => 'шестнадесет',
        17 => 'седемнадесет', 18 => 'осемнадесет', 19 => 'деветнадесет',
    ];

    /**
     * @param float|int|string $amount    Сумата (напр. 53.62)
     * @param string           $currency  'BGN' или 'EUR' (празно -> EUR)
     * @return string
     */
    public function toWords($amount, $currency)
    {
        $isBgn = ($currency === Contracts::CURRENCY_BGN);

        //Закръгляме до стотинки/центове, за да избегнем грешки с плаваща запетая
        $total = (int) round(((float) $amount) * 100);
        $whole = intdiv($total, 100);
        $frac  = $total % 100;

        if ($isBgn) {
            $wholeWords = $this->intToWords($whole, 'masc');
            $wholeUnit  = $this->endsInOne($whole) ? 'лев' : 'лева';
            $fracWords  = $this->intToWords($frac, 'fem');
            $fracUnit   = $this->endsInOne($frac) ? 'стотинка' : 'стотинки';
        } else {
            $wholeWords = $this->intToWords($whole, 'neut');
            $wholeUnit  = 'евро';
            $fracWords  = $this->intToWords($frac, 'masc');
            $fracUnit   = $this->endsInOne($frac) ? 'цент' : 'цента';
        }

        return $wholeWords . ' ' . $wholeUnit . ' и ' . $fracWords . ' ' . $fracUnit;
    }

    /**
     * Числото завършва ли на 1 (но не е 11) — тогава съществителното е в ед.ч.
     * напр. 1, 21, 31, 101 -> "лев"/"стотинка"; 11 -> "лева".
     */
    private function endsInOne($n)
    {
        return ($n % 10 === 1) && ($n % 100 !== 11);
    }

    /**
     * Цяло число 0..999999 -> думи, с род за последната единица.
     */
    private function intToWords($n, $gender)
    {
        if ($n === 0) {
            return 'нула';
        }

        $thousands = intdiv($n, 1000);
        $rest = $n % 1000;

        $thousandsWord = '';
        if ($thousands > 0) {
            if ($thousands === 1) {
                $thousandsWord = 'хиляда';
            } elseif ($thousands === 2) {
                $thousandsWord = 'две хиляди';
            } else {
                //родът за брояча на "хиляди" е женски (една/две хиляди);
                //при число, завършващо на 1, съществителното е в ед.ч. ("една хиляда")
                $thousandsUnit = $this->endsInOne($thousands) ? 'хиляда' : 'хиляди';
                $thousandsWord = $this->threeDigitsToWords($thousands, 'fem') . ' ' . $thousandsUnit;
            }
        }

        $restWord = $rest > 0 ? $this->threeDigitsToWords($rest, $gender) : '';

        if ($thousandsWord !== '' && $restWord !== '') {
            //"и" се поставя пред последния елемент, само ако той вече не съдържа "и"
            $sep = (strpos($restWord, ' и ') !== false) ? ' ' : ' и ';
            return $thousandsWord . $sep . $restWord;
        }

        return $thousandsWord . $restWord;
    }

    /**
     * Число 1..999 -> думи, с вътрешно "и" и род за единиците.
     */
    private function threeDigitsToWords($n, $gender)
    {
        $h = intdiv($n, 100);
        $r = $n % 100;

        $hundredsWord = $h > 0 ? self::$hundreds[$h] : '';
        $tensUnitsWord = $this->tensUnitsToWords($r, $gender);

        if ($hundredsWord !== '' && $tensUnitsWord !== '') {
            //"и" пред единиците, ако те са "атомарни" (под 20 или кръгли десетици)
            $atomic = ($r < 20) || ($r % 10 === 0);
            $sep = $atomic ? ' и ' : ' ';
            return $hundredsWord . $sep . $tensUnitsWord;
        }

        return $hundredsWord . $tensUnitsWord;
    }

    /**
     * Число 0..99 -> думи, с род за единиците.
     */
    private function tensUnitsToWords($r, $gender)
    {
        if ($r === 0) {
            return '';
        }
        if ($r < 10) {
            return $this->ones($r, $gender);
        }
        if ($r < 20) {
            return self::$teens[$r];
        }

        $t = intdiv($r, 10);
        $u = $r % 10;
        if ($u === 0) {
            return self::$tens[$t];
        }

        return self::$tens[$t] . ' и ' . $this->ones($u, $gender);
    }

    /**
     * Единици 1..9, с род за 1 и 2.
     */
    private function ones($u, $gender)
    {
        if ($u === 1) {
            if ($gender === 'fem') {
                return 'една';
            }
            if ($gender === 'neut') {
                return 'едно';
            }
            return 'един';
        }
        if ($u === 2) {
            return ($gender === 'masc') ? 'два' : 'две';
        }

        $map = [
            3 => 'три', 4 => 'четири', 5 => 'пет', 6 => 'шест',
            7 => 'седем', 8 => 'осем', 9 => 'девет',
        ];

        return $map[$u];
    }
}
