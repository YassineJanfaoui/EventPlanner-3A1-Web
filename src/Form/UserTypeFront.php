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
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

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
            'constraints' => [
                new NotBlank(['message' => 'Username is required']),
                new Length([
                    'min' => 3,
                    'minMessage' => 'Username must be at least {{ limit }} characters',
                    'max' => 20,
                    'maxMessage' => 'Username cannot exceed {{ limit }} characters',
                ]),
                new Regex([
                    'pattern' => '/^[A-Za-z_]+$/',
                    'message' => 'Username can only contain letters and underscores',
                ]),
            ],
            'row_attr' => ['class' => 'mb-3'],
        ])
        ->add('email', EmailType::class, [
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Enter email address',
                'autocomplete' => 'email'
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Email is required'
                ]),
                new EmailConstraint([
                    'message' => 'Please enter a valid email address'
                ]),
            ],
            'row_attr' => ['class' => 'mb-3'],
        ])
        ->add('name', TextType::class, [
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Enter full name'
            ],
            'constraints' => [
                new NotBlank(['message' => 'Name is required']),
                new Length([
                    'min' => 3,
                    'minMessage' => 'Name must be at least {{ limit }} characters'
                ]),
                new Regex([
                    'pattern' => '/^[A-Za-z\s\-]+$/',
                    'message' => 'Name must contain only letters, spaces, or hyphens',
                ]),
            ],
            'row_attr' => ['class' => 'mb-3'],
        ])
        ->add('phonenumber', TelType::class, [
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Enter phone number',
                'autocomplete' => 'tel'
            ],
            'constraints' => [
                new NotBlank(['message' => 'Phone number is required']),
                new Length([
                    'min' => 8,
                    'minMessage' => 'Phone number must be at least {{ limit }} digits',
                ]),
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
        ])
        ->add('plainPassword', PasswordType::class, [
            'mapped' => false,
            'required' => !$isEdit,
            'attr' => [
                'autocomplete' => 'new-password',
                'class' => 'form-control',
                'placeholder' => $isEdit ? 'Leave blank to keep current password' : 'Enter password'
            ],
            'constraints' => $isEdit ? [
                new Length([
                    'min' => 8,
                    'minMessage' => 'Password must be at least {{ limit }} characters',
                    'max' => 4096,
                ]),
                new Regex([
                    'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/',
                    'message' => 'Password must contain an uppercase letter, a lowercase letter, a number, and a special character.',
                ])
            ] : [
                new NotBlank(['message' => 'Please enter a password']),
                new Length([
                    'min' => 8,
                    'minMessage' => 'Password must be at least {{ limit }} characters',
                    'max' => 4096,
                ]),
                new Regex([
                    'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/',
                    'message' => 'Password must contain an uppercase letter, a lowercase letter, a number, and a special character.',
                ])
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