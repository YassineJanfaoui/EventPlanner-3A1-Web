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
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
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
                    new Regex([
                        'pattern' => '/^[a-zA-Z_]+$/',
                        'message' => 'Username must contain only letters and underscores',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Username must be at least {{ limit }} characters',
                        'max' => 50,
                        'maxMessage' => 'Username cannot exceed {{ limit }} characters'
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
                    new NotBlank(['message' => 'Email is required']),
                    new Email(['message' => 'Please enter a valid email address']),
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
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                        'message' => 'the name must contain only letters and spaces',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'The name must be at least {{ limit }} characters',
                        'max' => 50,
                        'maxMessage' => 'The name cannot exceed {{ limit }} characters'
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
                    new Regex([
                        'pattern' => '/^\D*(\d\D*){8,}$/',
                        'message' => 'Phone number must contain at least 8 digits',
                    ]),
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
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => !$isEdit,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control',
                    'placeholder' => $isEdit ? 'Leave blank to keep current password' : 'Enter password'
                ],
                'constraints' => $isEdit ? [
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/',
                        'message' => 'Password must contain at least 8 characters including upper/lowercase letters, a number and a symbol.',
                    ]),
                ] : [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/',
                        'message' => 'Password must contain at least 8 characters including upper/lowercase letters, a number and a symbol.',
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
