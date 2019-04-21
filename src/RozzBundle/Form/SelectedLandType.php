<?php

namespace RozzBundle\Form;

use RozzBundle\Entity\SelectedLand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectedLandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class'=>SelectedLand::class]);
    }

    public function getBlockPrefix()
    {
        return 'rozz_bundle_selected_land_type';
    }
}
