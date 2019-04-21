<?php

namespace RozzBundle\Form;

use RozzBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username',TextType::class, ['label'=>'Потребителско име :'])
            ->add('password',RepeatedType::class, ['type' => PasswordType::class,
                                                    'invalid_message' => 'Подадените пароли не съвпадат!',
                                                    'options' => array('attr' => array('class' => 'password-field')),
                                                    'required' => true,
                                                    'first_options'  => array('label' => 'Парола :'),
                                                    'second_options' => array('label' => 'Повтори паролата :'),
                                                     ])
            ->add('name',TextType::class, ['label'=>'Име :'])
            ->add('position', TextType::class, ['label'=>'Длъжност :']);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class'=>User::class]);
    }

    public function getBlockPrefix()
    {
        return 'rozz_bundle_user_type';
    }
}
