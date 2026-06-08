<?php

namespace RozzBundle\Form;

use RozzBundle\Entity\ContractTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Име на шаблона'])
            ->add('body', TextareaType::class, [
                'label' => 'Текст на договора',
                'attr'  => ['class' => 'wysiwyg', 'data-token-group' => 'body', 'rows' => 25],
            ])
            ->add('rowTemplate', TextareaType::class, [
                'label' => 'Шаблон за ред (един имот)',
                'attr'  => ['class' => 'wysiwyg', 'data-token-group' => 'row', 'rows' => 6],
            ])
            ->add('isActive', CheckboxType::class, [
                'label'    => 'Активен (използва се за нови договори)',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => ContractTemplate::class]);
    }

    public function getBlockPrefix()
    {
        return 'rozz_bundle_contract_template_type';
    }
}
