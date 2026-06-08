<?php

namespace RozzBundle\Controller;

use RozzBundle\Entity\Ntp;
use RozzBundle\Entity\Zem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Администраторски екран за стандартни цени по НТП и землище (матрица по землище).
 *
 * @Security("is_granted('ROLE_ADMIN')")
 */
class DefaultPricesController extends Controller
{
    /**
     * @Route("/settings/prices", name="default_prices_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $zems = $em->getRepository(Zem::class)->findBy([], ['name' => 'ASC']);

        $zemId = $request->query->get('zem');
        $selectedZem = $zemId ? $em->getRepository(Zem::class)->find($zemId) : null;

        $ntps = [];
        $prices = [];
        if ($selectedZem) {
            $ntps = $em->getRepository(Ntp::class)->findBy([], ['name' => 'ASC']);
            $prices = $this->get('default_price_service')->getPricesForZem($selectedZem);
        }

        return $this->render('@Rozz/SettingsView/default_prices.html.twig', [
            'zems'        => $zems,
            'selectedZem' => $selectedZem,
            'ntps'        => $ntps,
            'prices'      => $prices,
        ]);
    }

    /**
     * Записва матрицата (формата е POST).
     *
     * @Route("/settings/prices/{zemId}", name="default_prices_save")
     */
    public function saveAction(Request $request, $zemId)
    {
        $em = $this->getDoctrine()->getManager();
        $zem = $em->getRepository(Zem::class)->find($zemId);
        if (!$zem) {
            throw $this->createNotFoundException('Землището не е намерено.');
        }

        //масив ntp_id => цена от полетата price[ntp_id]
        $pricesByNtpId = $request->request->get('price', []);
        $this->get('default_price_service')->saveMatrix($zem, $pricesByNtpId);

        $this->addFlash('success', 'Цените за землище „' . $zem->getName() . '“ са запазени.');

        return $this->redirectToRoute('default_prices_index', ['zem' => $zem->getId()]);
    }
}
