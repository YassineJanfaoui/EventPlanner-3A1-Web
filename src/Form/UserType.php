<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter username'
                ],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter email address'
                ],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter full name'
                ],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('phonenumber', TelType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter phone number'
                ],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Active' => 'active',
                    'Inactive' => 'inactive',
                    'Banned' => 'banned',
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Simple User' => 'SIMPLE_USER',
                    'Administrator' => 'ADMIN',
                    'Event Planner' => 'EVENT_PLANNER',
                    'Team Leader' => 'TEAM_LEADER',
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'row_attr' => ['class' => 'mb-3'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => ['class' => 'needs-validation', 'novalidate' => 'novalidate'],
        ]);
    }
}