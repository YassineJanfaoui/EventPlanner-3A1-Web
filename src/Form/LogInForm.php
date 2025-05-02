<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class LogInForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Username',
                    'required' => true,
                    'autofocus' => true,
                ]
            ])
            ->add('_password', PasswordType::class, [
                'label' => false,
                'required' => false, // Make password optional
                'attr' => [
                    'placeholder' => 'Password',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'authenticate',
            'last_username' => '',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
