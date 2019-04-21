<?php

namespace RozzBundle\Form;

use RozzBundle\Entity\Examiners;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExaminerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',TextType::class,['label'=>'Име :', 'required'=>true])
            ->add('position',TextType::class,['label'=>'Позиция :']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class'=>Examiners::class]);
    }

    public function getBlockPrefix()
    {
        return 'rozz_bundle_examiner_type';
    }
}
