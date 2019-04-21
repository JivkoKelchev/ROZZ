<?php

namespace RozzBundle\Form;

use RozzBundle\Entity\Lands;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('num',TextType::class, ['label'=>'Номер на Имота:'])
            ->add('mest',ChoiceType::class, ['label'=>'Местност:','choices'=>
            $options['mests'],'choice_label'=>function($zem){return $zem->getName();}
            ])
            ->add('newMest',TextType::class,['label'=>'Нова местност:','required'=>false
            ])
            ->add('zem', ChoiceType::class,['label'=>'Землище:','choices'=>
                $options['zems'],'choice_label'=>function($zem){return $zem->getName();}
            ])
            ->add('newZem',TextType::class,['label'=>'Ново Землище:','required'=>false])
            ->add('ntp', ChoiceType::class,['label'=>'НТП:','choices'=>
                $options['ntps'],'choice_label'=>function($zem){return $zem->getName();}
            ])
            ->add('newNtp',TextType::class,['label'=>'Нов НТП:','required'=>false])
            ->add('kat', ChoiceType::class,['label'=>'Категория на земята:','choices'=>
                $options['kats'],'choice_label'=>function($zem){return $zem->getName();}
            ])
            ->add('newKat',TextType::class,['label'=>'Нова категория на земята','required'=>false])
            ->add('doc', TextType::class, ['label'=>'Номер на заповед/документ за собственост:'])
            ->add('area', TextType::class,['label'=>'Площ в дка :'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class'=>Lands::class,'mests'=>null,'zems'=>null,'ntps'=>null,'kats'=>null]);

    }

    public function getBlockPrefix()
    {
        return 'rozz_bundle_land_type';
    }
}
