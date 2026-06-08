<?php

namespace RozzBundle\Controller;

use RozzBundle\Entity\Contracts;
use RozzBundle\Entity\ContractTemplate;
use RozzBundle\Form\ContractTemplateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Администраторски екран за управление на шаблоните за договори.
 *
 * @Security("is_granted('ROLE_ADMIN')")
 */
class ContractTemplatesController extends Controller
{
    /**
     * @Route("/settings/templates", name="contract_templates_list")
     */
    public function listAction()
    {
        $service = $this->get('contract_template_service');
        $templates = $service->listAll();

        $usage = [];
        foreach ($templates as $template) {
            $usage[$template->getId()] = $service->countContractsUsing($template);
        }

        return $this->render('@Rozz/SettingsView/contract_templates_list.html.twig', [
            'templates' => $templates,
            'usage'     => $usage,
            'legacyId'  => $service->getLegacyTemplate() ? $service->getLegacyTemplate()->getId() : null,
        ]);
    }

    /**
     * @Route("/settings/templates/new", name="contract_templates_new")
     */
    public function newAction(Request $request)
    {
        $service = $this->get('contract_template_service');

        //Започваме от активния шаблон, за да може админът да редактира текущия текст
        $active = $service->getActiveTemplate();
        $template = new ContractTemplate();
        $template->setName('');
        $template->setBody($active->getBody());
        $template->setRowTemplate($active->getRowTemplate());
        $template->setIsActive(false);

        $form = $this->createForm(ContractTemplateType::class, $template);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $template->setCreatedAt(new \DateTime());
            $em->persist($template);
            $em->flush();
            if ($template->getIsActive()) {
                $service->activate($template);
            }
            $this->addFlash('success', 'Шаблонът е създаден.');

            return $this->redirectToRoute('contract_templates_list');
        }

        return $this->render('@Rozz/SettingsView/contract_template_form.html.twig', [
            'form'    => $form->createView(),
            'heading' => 'Нов шаблон',
            'tokens'  => $this->tokenReference(),
        ]);
    }

    /**
     * @Route("/settings/templates/{id}/edit", name="contract_templates_edit")
     */
    public function editAction(Request $request, $id)
    {
        $service = $this->get('contract_template_service');
        $em = $this->getDoctrine()->getManager();

        $original = $em->getRepository(ContractTemplate::class)->find($id);
        if (!$original) {
            throw $this->createNotFoundException('Шаблонът не е намерен.');
        }

        //Формата работи върху отделен обект, за да не променим оригинала, ако
        //трябва да създадем нова версия.
        $edited = new ContractTemplate();
        $edited->setName($original->getName());
        $edited->setBody($original->getBody());
        $edited->setRowTemplate($original->getRowTemplate());
        $edited->setIsActive($original->getIsActive());

        $form = $this->createForm(ContractTemplateType::class, $edited);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $boundCount = $service->countContractsUsing($original);

            if ($boundCount > 0) {
                //Има вече създадени договори с този шаблон -> нова версия,
                //за да запазят старите договори оригиналния си изглед.
                $edited->setCreatedAt(new \DateTime());
                $em->persist($edited);
                $em->flush();
                $service->activate($edited);
                $this->addFlash('success', 'Създадена е нова версия на шаблона и е активирана. Съществуващите договори запазват стария си вид.');
            } else {
                //Никой договор не го ползва -> редактираме на място.
                $original->setName($edited->getName());
                $original->setBody($edited->getBody());
                $original->setRowTemplate($edited->getRowTemplate());
                $em->flush();
                if ($edited->getIsActive()) {
                    $service->activate($original);
                }
                $this->addFlash('success', 'Шаблонът е обновен.');
            }

            return $this->redirectToRoute('contract_templates_list');
        }

        return $this->render('@Rozz/SettingsView/contract_template_form.html.twig', [
            'form'    => $form->createView(),
            'heading' => 'Редакция на шаблон: ' . $original->getName(),
            'tokens'  => $this->tokenReference(),
        ]);
    }

    /**
     * @Route("/settings/templates/{id}/activate", name="contract_templates_activate")
     */
    public function activateAction($id)
    {
        $service = $this->get('contract_template_service');
        $template = $this->getDoctrine()->getManager()
            ->getRepository(ContractTemplate::class)->find($id);
        if (!$template) {
            throw $this->createNotFoundException('Шаблонът не е намерен.');
        }
        $service->activate($template);
        $this->addFlash('success', 'Шаблонът е активиран.');

        return $this->redirectToRoute('contract_templates_list');
    }

    /**
     * @Route("/settings/templates/{id}/delete", name="contract_templates_delete")
     */
    public function deleteAction($id)
    {
        $service = $this->get('contract_template_service');
        $em = $this->getDoctrine()->getManager();
        $template = $em->getRepository(ContractTemplate::class)->find($id);
        if (!$template) {
            throw $this->createNotFoundException('Шаблонът не е намерен.');
        }

        if ($service->isLegacy($template)) {
            $this->addFlash('error', 'Наследеният шаблон не може да бъде изтрит.');

            return $this->redirectToRoute('contract_templates_list');
        }
        if ($service->countDirectlyBound($template) > 0) {
            $this->addFlash('error', 'Шаблонът се използва от договори и не може да бъде изтрит.');

            return $this->redirectToRoute('contract_templates_list');
        }

        $wasActive = $template->getIsActive();
        $em->remove($template);
        $em->flush();
        if ($wasActive) {
            //Връщаме активността към наследения шаблон.
            $service->activate($service->getLegacyTemplate());
        }
        $this->addFlash('success', 'Шаблонът е изтрит.');

        return $this->redirectToRoute('contract_templates_list');
    }

    /**
     * Преглед на шаблон с данни от последния създаден договор.
     *
     * @Route("/settings/templates/{id}/preview", name="contract_templates_preview")
     */
    public function previewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $template = $em->getRepository(ContractTemplate::class)->find($id);
        if (!$template) {
            throw $this->createNotFoundException('Шаблонът не е намерен.');
        }

        $contracts = $em->getRepository(Contracts::class)->findBy([], ['id' => 'DESC'], 1);
        if (!$contracts) {
            $this->addFlash('error', 'Няма наличен договор за преглед.');

            return $this->redirectToRoute('contract_templates_list');
        }
        $contract = $contracts[0];
        if ($contract->getStart() == null) {
            $start = clone $contract->getExpire();
            $start->modify('-1 year')->modify('+1 day');
            $contract->setStart($start);
        }
        $neighbours = $contract->getNeighbours(true);
        foreach ($contract->getUsedArea() as $area) {
            if (is_array($neighbours) && isset($neighbours[$area->getLand()->getNum()])) {
                $area->setNeighbours($neighbours[$area->getLand()->getNum()]);
            }
        }

        $renderedContract = $this->get('contract_template_renderer')
            ->render($template, $contract, $contract->getUsedArea());

        return $this->render('@Rozz/SettingsView/contract_template_preview.html.twig', [
            'renderedContract' => $renderedContract,
            'template'         => $template,
        ]);
    }

    /**
     * Списък с наличните %токени% за справка в редактора.
     */
    private function tokenReference()
    {
        return [
            'body' => [
                '[основание]'         => 'Основание',
                '[решение]'           => 'Решение/Заповед',
                '[заявление]'         => 'Заявление',
                '[кмет]'              => 'Кмет (име)',
                '[наемател]'          => 'Наемател (име)',
                '[егн]'               => 'ЕГН/ЕИК на наемателя',
                '[адрес]'             => 'Адрес на наемателя',
                '[списък_имоти]'      => 'Списък с имоти',
                '[обща_площ]'         => 'Обща площ',
                '[обща_цена]'         => 'Годишна цена (число)',
                '[цена_словом]'       => 'Годишна цена словом',
                '[цена за целият период]' => 'Цена за целия период (число)',
                '[цена за целият период словом]' => 'Цена за целия период словом',
                '[валута]'            => 'Валута (лева/евро)',
                '[начална_дата]'      => 'Начална дата',
                '[крайна_дата]'       => 'Крайна дата',
                '[срок]'              => 'Срок (текст)',
                '[имот_имоти]'        => 'Фраза „имот/имоти“',
                '[екземпляри]'        => 'Брой екземпляри (текст)',
                '[изготвил]'          => 'Изготвил (име)',
                '[длъжност_изготвил]' => 'Изготвил (длъжност)',
                '[съгласували]'       => 'Съгласували (блок)',
            ],
            'row' => [
                '[пореден_номер]'      => 'Пореден №',
                '[Част от имот]'       => 'Имот / Част от имот (според площта)',
                '[имот_идентификатор]' => 'Идентификатор на имота',
                '[местност]'           => 'Местност',
                '[землище]'            => 'Землище',
                '[нтп]'                => 'НТП',
                '[категория]'          => 'Категория',
                '[площ]'               => 'Площ',
                '[цена_дка]'           => 'Цена за дка',
                '[мерна_единица]'      => 'Мерна единица (лв./дка)',
                '[съседи]'             => 'Съседи',
            ],
        ];
    }
}
