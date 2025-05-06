<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Event Name',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The event name is required.',
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'The name must be at least {{ limit }} characters long.',
                        'maxMessage' => 'The name cannot be longer than {{ limit }} characters.',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('startDate')
            ->add('endDate')
            ->add('maxParticipants', NumberType::class, [
                'label' => 'Maximum Participants',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Maximum participants is required.',
                    ]),
                    new Assert\Positive([
                        'message' => 'The number must be positive.',
                    ]),
                    new Assert\LessThanOrEqual([
                        'value' => 1000,
                        'message' => 'The maximum participants cannot exceed 1000.',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Description is required.',
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 1000,
                        'minMessage' => 'The description must be at least {{ limit }} characters long.',
                        'maxMessage' => 'The description cannot be longer than {{ limit }} characters.',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu',
                'attr' => [
                    'class' => 'hidden-field',
                    'style' => 'display: none;'
                ]
            ])
            ->add('fee', NumberType::class, [
                'label' => 'Participation Fee',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The fee is required.',
                    ]),
                    new Assert\PositiveOrZero([
                        'message' => 'The fee must be a positive number or zero.',
                    ]),
                    new Assert\LessThanOrEqual([
                        'value' => 1000,
                        'message' => 'The fee cannot exceed 1000.',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('image', FileType::class, [
                'label' => 'Event Image',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'An image is required.',
                    ]),
                    new Assert\File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG or GIF).',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('latitude', NumberType::class, [
                'attr' => [
                    'class' => 'hidden-field',
                    'style' => 'display: none;'
                ],

                'required' => false,
                'constraints' => [
                    new Assert\Range([
                        'min' => -90,
                        'max' => 90,
                        'notInRangeMessage' => 'La latitude doit être comprise entre -90 et 90 degrés.',
                    ]),
                ],
            ])
            ->add('longitude', NumberType::class, [
                'attr' => [
                    'class' => 'hidden-field',
                    'style' => 'display: none;'
                ],
                'required' => false,
                'constraints' => [
                    new Assert\Range([
                        'min' => -180,
                        'max' => 180,
                        'notInRangeMessage' => 'La longitude doit être comprise entre -180 et 180 degrés.',
                    ]),
                ],
            ])

            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}