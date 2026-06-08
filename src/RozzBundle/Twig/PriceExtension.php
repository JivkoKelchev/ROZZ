<?php

namespace RozzBundle\Twig;

use RozzBundle\Services\PriceToWordsService;

/**
 * Twig филтър за изписване на сума словом: {{ amount | priceInWords(currency) }}
 */
class PriceExtension extends \Twig_Extension
{
    /**
     * @var PriceToWordsService
     */
    private $priceToWords;

    public function __construct(PriceToWordsService $priceToWords)
    {
        $this->priceToWords = $priceToWords;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('priceInWords', [$this, 'priceInWords']),
        ];
    }

    public function priceInWords($amount, $currency)
    {
        return $this->priceToWords->toWords($amount, $currency);
    }

    public function getName()
    {
        return 'rozz_price_extension';
    }
}
