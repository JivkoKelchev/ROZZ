<?php

namespace RozzBundle\Form;

use Doctrine\ORM\EntityRepository;
use RozzBundle\Entity\Contracts;
use RozzBundle\Entity\Examiners;
use RozzBundle\Entity\NewContracts;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewContractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('resheniq', TextType::class,['label'=>'Решение/Заповед'])
            ->add('application',TextType::class,['label'=>'Заявление'])
            ->add('years', NumberType::class,['label'=>'За срок от [години]:'])
            ->add('neighbours', CollectionType::class,
                ['entry_type' => TextareaType::class,
                'label' => 'Описание на съседни имоти',
                'required'=> false])
            ->add('examiner', EntityType::class, ['label'=> 'Съгласувал', 'class'=> Examiners::class,
                                                     'choice_label'=> 'name'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class'=>NewContracts::class]);
    }

    public function getBlockPrefix()
    {
        return 'rozz_bundle_new_contract_type';
    }
}
