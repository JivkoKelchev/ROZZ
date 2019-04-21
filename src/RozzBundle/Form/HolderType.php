<?php

namespace RozzBundle\Form;

use RozzBundle\Entity\Holders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HolderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',TextType::class,['label'=>'Име :'])
                ->add('eGN',TextType::class,['label'=>'ЕГН :'])
                ->add('addres',TextType::class,['label'=>'Адрес :']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class'=>Holders::class]);
    }

    public function getBlockPrefix()
    {
        return 'rozz_bundle_holder_type';
    }
}
