<?php

namespace RozzBundle\Form;

use RozzBundle\Entity\Mayors;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MayorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, ['label'=>'Име :', 'required'=>true])
            ->add('eGN', TextType::class, ['label'=>'ЕГН :']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class'=>Mayors::class]);
    }

    public function getBlockPrefix()
    {
        return 'rozz_bundle_mayor_type';
    }
}
