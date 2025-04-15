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

class UserTypeFront extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter username',
                    'autocomplete' => 'username'
                ],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter email address',
                    'autocomplete' => 'email'
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
                    'placeholder' => 'Enter phone number',
                    'autocomplete' => 'tel'
                ],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Simple User' => 'SIMPLE_USER',
                    'Team Leader' => 'TEAM_LEADER',
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'row_attr' => ['class' => 'mb-3'],
            ]);

        // Password field configuration
        $builder->add('plainPassword', PasswordType::class, [
            'mapped' => false,
            'required' => !$isEdit,
            'attr' => [
                'autocomplete' => 'new-password',
                'class' => 'form-control',
                'placeholder' => $isEdit ? 'Leave blank to keep current password' : 'Enter password'
            ],
            'constraints' => $isEdit ? [
                new Length([
                    'min' => 6,
                    'minMessage' => 'Your password should be at least {{ limit }} characters',
                    'max' => 4096,
                ]),
            ] : [
                new NotBlank([
                    'message' => 'Please enter a password',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Your password should be at least {{ limit }} characters',
                    'max' => 4096,
                ]),
            ],
            'row_attr' => ['class' => 'mb-3'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
            'attr' => [
                'class' => 'needs-validation',
                'novalidate' => 'novalidate'
            ],
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}